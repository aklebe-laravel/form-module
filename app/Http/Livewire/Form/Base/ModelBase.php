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

    ///**
    // * The relation ids from sub data-table grids indexed by property name
    // * (same like laravel model relation like 'categories', 'mediaItems', 'users', ...)
    // *
    // * @var array
    // */
    //public array $relationUpdates = [];

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

            // format to id array
            $form->runObjectRelationsRootProperties($this->dataTransfer, function ($propertyKey, $dataInItems) use ($modelLoaded) {

                $idArray = [];
                // each relation like 'mediaItems'
                foreach ($this->dataTransfer[$propertyKey] as $v) {
                    $idArray[] = $v['id'];
                }
                $this->dataTransfer[$propertyKey] = $idArray;

            });

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
                        'parentData' => $this->parentData,
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
     * add an object with a field 'id'
     *
     * @param  string  $relationPath
     * @param  mixed   $itemId
     *
     * @return void
     */
    private function addIdToRelationIfNotExists(string $relationPath, mixed $itemId): void
    {
        if (isset($this->dataTransfer[$relationPath])) {
            foreach ($this->dataTransfer[$relationPath] as $v) {
                if ($v['id'] == $itemId) {
                    return;
                }
            }
            $this->dataTransfer[$relationPath][] = ['id' => $itemId];
        }
    }

    /**
     * @param  string  $relationPath
     * @param  array   $values
     * @param  bool    $skipRender
     *
     * @return void
     */
    #[On('update-relations')]
    public function updateRelations(string $relationPath, array $values, bool $skipRender = true): void
    {
        // check relation like 'mediaItems' exists
        if (isset($this->dataTransfer[$relationPath])) {
            $newRelation = [];
            // assign all values still present, so not presented values are deleted after ...
            foreach ($this->dataTransfer[$relationPath] as $v) {
                if (in_array($v['id'], $values)) {
                    $newRelation[] = $v;
                }
            }
            $this->dataTransfer[$relationPath] = $newRelation;

            foreach ($values as $v) {
                $this->addIdToRelationIfNotExists($relationPath, $v);
            }
        }

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
     * Overwrite this if needed!
     *
     * Upload relevant relation should be updated from here.
     * Otherwise, the form don't know about the (child) upload in \Modules\WebsiteBase\Http\Livewire\FilesUpload::finishUpload
     * and the relation to the uploaded image is lost.
     *
     * @param  mixed  $mediaItemId
     *
     * @return void
     */
    #[On('upload-process-finished')]
    public function uploadProcessFinished(mixed $mediaItemId): void
    {
        // don't miss the new relation by pressing accept/save form ...
        $this->addIdToRelationIfNotExists('mediaItems', $mediaItemId);

        $this->reopenFormIfNeeded();
    }

}
