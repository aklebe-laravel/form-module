<?php

namespace Modules\Form\app\Http\Livewire\Form\Base;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Resources\Json\JsonResource;
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
    public string $modelName = '';

    /**
     * The relation ids from sub data-table grids indexed by property name
     * (same like laravel model relation like 'categories', 'mediaItems', 'users', ...)
     *
     * @var array
     */
    public array $relationUpdates = [];

    /**
     * If given, it's the related datatable where this form is used to edit their items.
     * Used to refresh the related datatable after edited this form.
     *
     * @var string
     */
    public string $relatedLivewireDataTable = '';

    /**
     * External assigned form default values
     *
     * @var array
     */
    public array $objectInstanceDefaultValues = [];

    /**
     * @return string
     */
    public function getModelName(): string
    {
        if (!$this->modelName) {
            $this->modelName = $this->getFormName();
        }

        return $this->modelName;
    }

    /**
     * Calculating and generating the form object,
     * prepare formObjectAsArray
     * and adjust actionable and readonly flags depends on form config
     *
     * @return JsonResource|null
     * @todo: replace and resolve
     */
    protected function getForm(): ?JsonResource
    {
        if (!$this->isFormOpen) {
            return null;
        }

        $this->_form = ModelBaseAlias::getFormInstance($this->getFormName());
        if (!$this->_form) {
            Log::error(sprintf("Form '%s' not found!", $this->getFormName()));
            return null;
        }

        // assign parent data from form livewire to form
        $this->_form->parentData = app('system_base')->arrayMergeRecursiveDistinct($this->_form->parentData,
            $this->parentData);

        // assign this to form
        $this->_form->setLiveWireId($this->getId());

        // assign default values for object model instance
        $this->_form->objectInstanceDefaultValues = app('system_base')->arrayMergeRecursiveDistinct($this->_form->objectInstanceDefaultValues,
            $this->objectInstanceDefaultValues);

        // calculate and render form
        $this->_formResult = $this->_form->renderWithResource($this->formObjectId);

        // after form calculation, adjust permissions
        $this->readonly = !$this->_form->canEdit();
        $this->actionable = $this->_form->canEdit();

        // @todo: object deprecated change to form_object.jsonResource?
        // Important to check if $this->formObjectAsArray was already filled!
        if (!$this->formObjectAsArray) {
            if ($object = data_get($this->_formResult, 'additional.form_object.object')) {
                $this->formObjectAsArray = app('system_base')->toArray($object);
            } else {
                $this->formObjectAsArray = [];
            }
        }

        return $this->_formResult;
    }

    /**
     * @param $id
     *
     * @return void
     */
    #[On('duplicate-and-open-form')]
    public function duplicateAndOpenForm($id): void
    {
        if ($item = app('system_base')->getEloquentModelBuilder($this->getModelName())->whereKey($id)->first()) {

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
     * @throws \Exception
     */
    #[On('delete-item')]
    public function deleteItem(mixed $livewireId, mixed $itemId): bool
    {
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }

        if ($this->_form->jsonResource) {
            if ($this->_form->jsonResource->delete()) {
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
        /** @var \Modules\Form\app\Forms\Base\ModelBase $form */
        $form = $this->getFormInstance();
        // Model have to exists ...
        if ($modelLoaded = $form->getJsonResource($this->formObjectId)) {

            foreach ($this->relationUpdates as $propertyInCamelCase => $relationIdUpdates) {

                $originalRelationIds = $modelLoaded->$propertyInCamelCase()
                    ->pluck($modelLoaded->getKeyName())
                    ->toArray();

                if ((count($relationIdUpdates) > 0) || (count($relationIdUpdates) != count($originalRelationIds))) {
                    $this->formObjectAsArray[$propertyInCamelCase] = $relationIdUpdates;
                } else {
                    $this->formObjectAsArray[$propertyInCamelCase] = $originalRelationIds;
                }
            }

            try {

                $jsonResponse = new JsonViewResponse();
                $validatedData = $form->validate($this->formObjectAsArray, $jsonResponse);
                if (!$validatedData || $jsonResponse->hasErrors()) {
                    $this->addErrorMessages($jsonResponse->getErrors());
                }

                return $validatedData;

            } catch (\Exception $exception) {

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
        /** @var \Modules\Form\app\Forms\Base\ModelBase $form */
        $form = $this->getFormInstance();

        if ($validatedData = $this->validateForm()) {

            try {

                $updateList = [
                    [
                        'data'       => $this->formObjectAsArray,
                        'parentData' => $this->parentData
                    ],

                ];

                return $form->runUpdateList($updateList);

            } catch (\Exception $exception) {

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
     * Emit
     *
     * @param  mixed  $livewireId
     * @param  mixed  $itemId
     * @return void
     */
    public function save(mixed $livewireId, mixed $itemId): void
    {
        if (!$this->checkLivewireId($livewireId)) {
            return;
        }

        $res = $this->saveFormData();
        if (!$res->hasErrors()) {
            $this->addSuccessMessage(__('Data saved successfully.'));

            // If related datatable exists, we want to close the form.
            // Otherwise, do not close form if no table present (like user profile)
            if ($this->relatedLivewireDataTable) {
                $this->closeFormAndRefreshDatatable();
            } else {
                $this->reopenFormIfNeeded(true);
            }
        } else {
            $this->addErrorMessages($res->getErrors());

            // Open this form again (with errors)!
            $this->reopenFormIfNeeded();
        }
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
     * Emit to close form and refresh datatable if present.
     *
     * @return void
     */
    protected function closeFormAndRefreshDatatable(): void
    {
        // close this form
        $this->closeForm();

        // refresh data-table
        $this->refreshDatatable();
    }

    /**
     * Emit to close form and refresh datatable if present.
     *
     * @return void
     */
    protected function refreshDatatable(): void
    {
        // refresh data-table
        if ($this->relatedLivewireDataTable) {
            $this->dispatch('refresh')->to($this->relatedLivewireDataTable);
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
