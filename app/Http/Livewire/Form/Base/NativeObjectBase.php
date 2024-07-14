<?php

namespace Modules\Form\app\Http\Livewire\Form\Base;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Modules\Form\app\Forms\Base\ModelBase;
use Modules\SystemBase\app\Http\Livewire\BaseComponent;
use Modules\SystemBase\app\Models\JsonViewResponse;

class NativeObjectBase extends BaseComponent
{
    use WithFileUploads;

    /**
     * The form is closed by default.
     *
     * @var bool
     */
    public bool $isFormOpen = false;

    /**
     * Decides form can send by key ENTER
     *
     * @var bool
     */
    public bool $canKeyEnterSendForm = false;

    /**
     * Enable/disable form actions.
     * Usually changed as livewire parameter.
     *
     * @var bool
     */
    public bool $actionable = true;

    /**
     * Show form as readonly (background).
     * Usually changed as livewire parameter.
     *
     * @var bool
     */
    public bool $readonly = false;

    /**
     * Will get form also in hydrate instead of mount() only
     *
     * @var bool
     */
    public bool $formInHydrate = true;

    /**
     * @var string
     */
    public string $title = '';

    /**
     * @var string
     */
    public string $formName = '';

    /**
     * The model id to load.
     *
     * @var mixed
     */
    public mixed $formObjectId = null;

    /**
     * The data container from the db model
     *
     * @var array
     */
    public array $formObjectAsArray = [];

    /**
     * @var array|string[]
     */
    public array $formActionButtons = [
        'cancel' => 'form::components.form.actions.defaults.cancel',
        'delete' => 'form::components.form.actions.defaults.delete',
        'accept' => 'form::components.form.actions.defaults.accept',
    ];

    /**
     * @var \Modules\Form\app\Forms\Base\ModelBase|null
     */
    protected ?\Modules\Form\app\Forms\Base\NativeObjectBase $_form = null;

    /**
     * @var JsonResource|null
     */
    protected ?JsonResource $_formResult = null;

    /**
     * if true adding
     * x-data="{form_data:$wire.formObjectAsArray}"
     * to form
     *
     * @var bool
     */
    public bool $autoXData = false;

    /**
     * Overwrite this to setup the default Call if Enter pressed in Form
     *
     * @return string
     */
    protected function getDefaultWireFormAccept(): string
    {
        // maybe this version is also correct?
        return $this->getWireCallString('save', [data_get($this->formObjectAsArray, 'id', '')]);

        //        $editForm = $this->getFormResult();
        //        $editFormObject = data_get($editForm, 'additional.form_object');
        //        $editFormModelObject = data_get($editFormObject, 'object');
        //        return $this->getWireCallString('save', [data_get($editFormModelObject, 'id', '')]);
    }

    /**
     * Overwrite this to setup the default Call if Esc pressed in Form
     *
     * @return string
     */
    protected function getDefaultWireFormCancel(): string
    {
        return $this->getWireCallString('closeForm');
    }

    /**
     * @return JsonResource|null
     */
    public function getFormResult(): ?JsonResource
    {
        return $this->_formResult;
    }

    /**
     * @return void
     */
    public function resetFormResult(): void
    {
        $this->_formResult = null;
    }

    /**
     * @return void
     */
    protected function initMount(): void
    {
        parent::initMount();

        /**
         * @internal If place it in boot or hydrate, we get js console error "Uncaught Component not found: xxx"
         * and nothing is working anymore. So mount can be the only valid place.
         */
        $this->reopenFormIfNeeded();
    }

    /**
     * @return void
     */
    public function resetMessages(): void
    {
        parent::resetMessages();

        // @todo: perform this way?!
        $this->reopenFormIfNeeded();
    }

    /**
     * @return string
     */
    public function getFormName(): string
    {
        if (!$this->formName) {
            $this->formName = app('system_base')->getSimpleClassName(static::class);
        }

        return $this->formName;
    }

    /**
     * @param  string  $formName
     *
     * @return ModelBase|mixed
     */
    public function getFormInstance(string $formName = ''): mixed
    {
        if (!$formName) {
            $formName = $this->getFormName();
        }
        return ModelBase::getFormInstance($formName);
    }

    /**
     * Overwrite if needed.
     *
     * @return void
     */
    protected function beforeRender(): void
    {
        //        $this->getForm();
    }

    /**
     * @return Application|Factory|View
     */
    public function render(): View|Factory|Application
    {
        //
        $this->beforeRender();

        //
        return view('form::livewire.forms.default-native-object');
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

        $this->_form = ModelBase::getFormInstance($this->getFormName());
        if (!$this->_form) {
            Log::error(sprintf("Form '%s' not found!", $this->getFormName()));
            return null;
        }

        // assign parent data from form livewire to form
        $this->_form->parentData = app('system_base')->arrayMergeRecursiveDistinct($this->_form->parentData,
            $this->parentData, false);

        // assign this to form
        $this->_form->setLiveWireId($this->getId());

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
     * @return bool
     */
    protected function isFormCreated() : bool
    {
        return (bool)$this->_formResult;
    }

    /**
     * @param  bool  $forceReset
     * @return void
     */
    protected function reopenFormIfNeeded(bool $forceReset = false): void
    {
        if ($this->isFormOpen && !$this->isFormCreated()) {
            $this->openForm($this->formObjectId, $forceReset);
        }
    }

    protected function getComponentFormName(): string
    {
        return 'form.'.\Illuminate\Support\Str::snake($this->getFormName(), '-');
    }

    /**
     * Should be overwritten.
     *
     * @return bool
     */
    protected function afterOpenForm(): bool
    {
        // call js function formOpened() ...
        $this->dispatch('formOpened');

        return true;
    }

    /**
     * @param $id
     * @param  bool  $forceReset
     * @return void
     */
    #[On('open-form')]
    public function openForm($id, bool $forceReset = true): void
    {
        if ($forceReset) {
            $this->resetFormResult();
            $this->formObjectAsArray = [];
        }

        $this->isFormOpen = true;
        $this->formObjectId = $id;

        // calculate and generate form
        $this->getForm();

        // event
        $this->afterOpenForm();

        // assign form actions after form was calculated and generated
        $this->formActionButtons = $this->calcFormActionButtons();
    }

    /**
     * @return void
     */
    #[On('close-form')]
    public function closeForm(): void
    {
        $this->isFormOpen = false;
    }

    /**
     * Should be overwritten.
     *
     * @return array|string[]
     */
    protected function calcFormActionButtons(): array
    {
        return $this->formActionButtons;
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

        // no default code so far ...

        return true;
    }
}
