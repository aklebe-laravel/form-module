<?php

namespace Modules\Form\app\Http\Livewire\Form\Base;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Modules\Form\app\Forms\Base\NativeObjectBase as NativeObjectBaseForm;
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
     * Current data object for all relevant data in background.
     *
     * @var JsonResource|null
     */
    protected JsonResource|null $dataSource = null;

    /**
     * The data as array (for example from the db model) resulted from $this->dataSource
     * which is synced with frontend and input elements.
     * Names if input will prefix with 'dataTransfer.xxx'
     *
     * @var array
     */
    public array $dataTransfer = [];

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
     * Nested assoc array of elements updated by livewire updating()
     *
     * @var array
     */
    public array $liveUpdate = [];


    /**
     * @var array
     */
    public array $activeTabs = [];

    /**
     * @var array|string[]
     */
    public array $formActionButtons = [
        'cancel' => 'form::components.form.actions.defaults.cancel',
        'delete' => 'form::components.form.actions.defaults.delete',
        'accept' => 'form::components.form.actions.defaults.accept',
    ];

    /**
     * cached form by calling getFormInstance()
     *
     * @var NativeObjectBaseForm|null
     */
    private ?NativeObjectBaseForm $_form = null;

    /**
     * @var JsonResource|null
     */
    protected ?JsonResource $_formResult = null;

    /**
     * if true adding
     * x-data="{form_data:$wire.dataTransfer}"
     * to form
     *
     * @var bool
     */
    public bool $autoXData = false;

    /**
     * @return JsonResource|null
     */
    public function getDataSource(): ?JsonResource
    {
        return $this->dataSource;
    }

    /**
     * @param  JsonResource|null  $dataSource
     *
     * @return void
     */
    public function setDataSource(?JsonResource $dataSource): void
    {
        $this->dataSource = $dataSource;
    }

    /**
     * Overwrite for extra logic.
     *
     * @param  mixed|null  $id
     *
     * @return JsonResource
     */
    public function initDataSource(mixed $id = null): JsonResource
    {
        $this->dataSource = $this->dataSource ?: new JsonResource([]);

        return $this->dataSource;
    }

    /**
     * @param $property
     * @param $value
     *
     * @return void
     */
    public function updating($property, $value): void
    {
        $propertyPrepared = Str::chopStart($property, 'dataTransfer.');
        if (Arr::has($this->liveUpdate, $propertyPrepared)) {
            data_set($this->liveUpdate, $propertyPrepared, $value);
            $this->reopenFormIfNeeded(true); // true is important to update all values!
        }
    }

    /**
     * Default values used set up missing prepared values and to create new object instance.
     * Overwrite this by calling parent::makeObjectInstanceDefaultValues()
     * or overwrite $this->objectInstanceDefaultValues.
     *
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return $this->objectInstanceDefaultValues;
    }

    /**
     * Overwrite this to set up the default call if enter was pressed in form
     *
     * @return string
     */
    protected function getDefaultWireFormAccept(): string
    {
        // maybe this version is also correct?
        return $this->getWireCallString('save', [data_get($this->dataTransfer, 'id', '')]);
    }

    /**
     * Overwrite this to set up the default call if esc pressed in form
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
     * Destroy all relevant data from previous opened form.
     * Should be overwritten if there is more to reset.
     *
     * @return void
     */
    public function resetFormRelevantData(): void
    {
        $this->_formResult = null;
        $this->dataTransfer = [];
        $this->dataSource = null;
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
     * Get the form by model name without namespace and find namespace automatically.
     * See Modules/Form/Config/config.php for details.
     * Instance is calculated once and will be cached for the current process.
     *
     * @param  string  $formName  just the form name without namespace
     *
     * @return NativeObjectBaseForm|null
     */
    public function getFormInstance(string $formName = ''): ?NativeObjectBaseForm
    {
        if ($this->_form) {
            return $this->_form;
        }

        if ($modelName = app('system_base')->findModuleClass($formName ?: $this->getFormName(), 'model-forms')) {
            try {
                $this->_form = App::make($modelName);

                // assign this as livewire form
                $this->_form->formLivewire = $this;

                // set up defaults
                $this->objectInstanceDefaultValues = $this->_form->makeObjectInstanceDefaultValues();

                // return form
                return $this->_form;
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }

        return null;
    }


    /**
     * Overwrite if needed.
     *
     * @return void
     */
    protected function beforeRender(): void
    {
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
     * prepare dataTransfer
     * and adjust actionable and readonly flags depends on form config
     *
     * @return JsonResource|null
     * @todo: replace and resolve
     */
    protected function generateFormResult(): ?JsonResource
    {
        if (!$this->isFormOpen) {
            return null;
        }

        if (!($form = $this->getFormInstance())) {
            Log::error(sprintf("Form '%s' not found!", $this->getFormName()));

            return null;
        }

        // calculate and render form
        $this->_formResult = $form->renderWithResource($this->formObjectId);

        // after form calculation, adjust permissions
        $this->readonly = !$form->canEdit();
        $this->actionable = $form->canEdit();

        // Important to check if $this->dataTransfer was already filled!
        if (!$this->dataTransfer) {
            $this->refreshTransferData();
        }

        return $this->_formResult;
    }

    /**
     * create or renew dataTransfer based on dataSource
     *
     * @return void
     */
    public function refreshTransferData(): void
    {
        //if ($object = data_get($this->_formResult, 'additional.final_form_elements.object')) {
        if ($object = $this->getDataSource()) {
            $this->dataTransfer = app('system_base')->toArray($object);
        } else {
            $this->dataTransfer = [];
        }
    }

    /**
     * @return bool
     */
    protected function isFormCreated(): bool
    {
        return (bool) $this->_formResult;
    }

    /**
     * @param  bool  $forceReset
     *
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
        return 'form.'.Str::snake($this->getFormName(), '-');
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
     * @param  mixed  $id  can also be an array (i.e.: ratings)
     * @param  bool   $forceReset
     *
     * @return void
     */
    #[On('open-form')]
    public function openForm(mixed $id, bool $forceReset = true): void
    {
        if ($forceReset || !$id) {
            // completely destroy data from old form ...
            $this->resetFormRelevantData();
        }

        $this->isFormOpen = true;
        $this->formObjectId = $id;

        // calculate and generate form
        $this->generateFormResult();

        // event
        $this->afterOpenForm();

        // assign form actions after form was calculated and generated
        $this->formActionButtons = $this->calcFormActionButtons();
    }

    /**
     * @param  string  $tabControl
     * @param  string  $tabPage
     *
     * @return void
     */
    #[On('switch-tab')]
    public function switchTab(string $tabControl, string $tabPage): void
    {
        $this->activeTabs[$tabControl] = $tabPage;
        $this->reopenFormIfNeeded();
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
     * Called by save() or other high level calls.
     *
     * @return JsonViewResponse
     */
    protected function saveFormData(): JsonViewResponse
    {
        // Take the form again to use their validator and update functionalities ...
        /** @var NativeObjectBaseForm $form */
        $form = $this->getFormInstance();

        $jsonResponse = new JsonViewResponse();
        if ($validatedData = $this->validateForm()) {

            // ...

            $jsonResponse->setMessage('Validation ok.');
        } else {
            $jsonResponse->setErrorMessage('Unable to load data or validation error.');

            return $jsonResponse;
        }

        $jsonResponse->setErrorMessage('Need to overwrite saveFormData()');

        return $jsonResponse;
    }

    /**
     * Emit
     *
     * @param  mixed  $livewireId
     * @param  mixed  $itemId
     *
     * @return void
     */
    public function save(mixed $livewireId, mixed $itemId): void
    {
        if (!$this->checkLivewireId($livewireId)) {
            return;
        }

        $res = $this->saveFormData();
        if (!$res->hasErrors()) {

            if ($res->getMessage()) {
                $this->addSuccessMessage($res->getMessage());
            } else {
                $this->addSuccessMessage(__('Data saved successfully.'));
            }

            // After saving was successful, refresh the transferData.
            // Otherwise, a reopened form has outdated data!
            //
            //$this->dataTransfer = [];
            $this->refreshTransferData();

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
     * @return array
     */
    public function validateForm(): array
    {
        // Take the form again to use their validator and update functionalities ...
        $form = $this->getFormInstance();
        // Model have to exists ...
        if ($form->initDataSource($this->formObjectId)) {

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

        // no default code so far ...

        return true;
    }
}
