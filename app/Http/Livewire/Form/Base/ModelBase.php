<?php

namespace Modules\Form\app\Http\Livewire\Form\Base;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Modules\Form\app\Forms\Base\ModelBase as ModelBaseAlias;
use Modules\SystemBase\app\Models\JsonViewResponse;

/**
 * the Livewire Form Part.
 */
class ModelBase extends NativeObjectBase
{
    use WithFileUploads;

    /**
     * @todo: Maybe its no longer needed?
     *
     * @var string
     */
    public string $eloquentModelName = '';

    /**
     * The relation ids from sub data-table grids indexed by property name
     * (same like laravel model relation like 'categories', 'mediaItems', 'users', ...)
     *
     * @var array
     */
    public array $relationUpdates = [];

    /**
     * @return string
     */
    public function getEloquentModelName(): string
    {
        if (!$this->eloquentModelName) {
            $this->eloquentModelName = $this->getFormName();
        }

        return $this->eloquentModelName;
    }

    /**
     * @param  string|int  $id
     *
     * @return void
     * @throws BindingResolutionException
     */
    #[On('duplicate-and-open-form')]
    public function duplicateAndOpenForm(string|int $id): void
    {
        if ($item = app('system_base')->getEloquentModelBuilder($this->getEloquentModelName())->whereKey($id)->first()) {

            $methodReplicateRelations = 'replicateWithRelations';
            if (method_exists($item, $methodReplicateRelations)) {
                $newItem = $item->$methodReplicateRelations();
                // refresh table ...
                $this->refreshDatatable();
                // open form ...
                $this->openForm($newItem->getKey());
            } else {
                $this->addErrorMessage('Unable to duplicate.');
                Log::error("Method not found. Use TraitBaseModel!", [$methodReplicateRelations, __METHOD__]);
            }


        } else {
            $this->addErrorMessage('Unable to duplicate.');
            Log::error("Item not found", [$id, __METHOD__]);
        }

    }

    /**
     * @param  mixed  $livewireId
     * @param  mixed  $itemId
     *
     * @return bool
     * @throws Exception
     */
    #[On('delete-item')]
    public function deleteItem(mixed $livewireId, mixed $itemId): bool
    {
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }

        if ($this->dataSource) {
            if ($this->dataSource->delete()) {
                $this->addSuccessMessage("Item deleted.");
            }
        }

        // close form after deleting
        $this->closeFormAndRefreshDatatable();
        return true;
    }

    /**
     * @return array
     */
    public function validateForm(): array
    {
        // Take the form again to use their validator and update functionalities ...
        /** @var ModelBaseAlias $form */
        $form = $this->getFormInstance();
        // Model have to exists ...
        if ($modelLoaded = $form->initDataSource($this->formObjectId)) {

            foreach ($this->relationUpdates as $propertyInCamelCase => $relationIdUpdates) {

                $originalRelationIds = $modelLoaded->$propertyInCamelCase()
                    ->pluck($modelLoaded->getKeyName())
                    ->toArray();

                if ((count($relationIdUpdates) > 0) || (count($relationIdUpdates) != count($originalRelationIds))) {
                    $this->dataTransfer[$propertyInCamelCase] = $relationIdUpdates;
                } else {
                    $this->dataTransfer[$propertyInCamelCase] = $originalRelationIds;
                }
            }

            try {

                $jsonResponse = new JsonViewResponse();
                $validatedData = $form->validate($this->dataTransfer, $jsonResponse);
                if (!$validatedData || $jsonResponse->hasErrors()) {
                    $this->addErrorMessages($jsonResponse->getErrors());
                }

                return $validatedData;

            } catch (Exception $exception) {

                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());

                $this->addErrorMessage('Unable to validate Data.');

            }

        }

        return [];
    }

    /**
     * Called by save() or other high level calls.
     *
     * @return JsonViewResponse
     */
    protected function saveFormData(): JsonViewResponse
    {
        // Take the form again to use their validator and update functionalities ...
        /** @var ModelBaseAlias $form */
        $form = $this->getFormInstance();

        if ($validatedData = $this->validateForm()) {

            try {

                $updateList = [
                    [
                        'data'       => $this->dataTransfer,
                        'parentData' => $this->parentData
                    ],

                ];

                return $form->runUpdateList($updateList);

            } catch (Exception $exception) {

                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());

                $jsonResponse = new JsonViewResponse();
                $jsonResponse->setErrorMessage('Unable to save Model.');

                return $jsonResponse;

            }

        }

        $jsonResponse = new JsonViewResponse();
        $jsonResponse->setErrorMessage('Unable to load data or validation error.');
        return $jsonResponse;
    }

    /**
     * @param  string  $relationPath
     * @param  iterable  $values
     * @param  bool  $skipRender
     * @return void
     */
    #[On('update-relations')]
    public function updateRelations(string $relationPath, iterable $values, bool $skipRender = true): void
    {
        $this->relationUpdates[$relationPath] = $values;

        // avoid rerender form (and hide the current form tab)
        if ($skipRender) {
            $this->skipRender();
        }
    }

    /**
     * @return Application|Factory|View
     */
    public function render(): View|Factory|Application
    {
        //
        $this->beforeRender();

        //
        return view('form::livewire.forms.default-model-base');
    }

    /**
     * Called Livewire/FiledUpload
     *
     * Upload relevant relation should be updated from here.
     * Otherwise, the form don't know about the (child) upload in \Modules\WebsiteBase\Http\Livewire\FilesUpload::finishUpload
     * and the relation to the uploaded image is lost.
     *
     * @param  string  $event
     * @param  mixed  $mediaItemId
     * @return void
     */
    #[On('upload-process-finished')]
    public function uploadProcessFinished(string $event, mixed $mediaItemId): void
    {
        $this->reopenFormIfNeeded();
    }

}
