<?php

namespace Modules\Form\app\Http\Livewire\Form\Base;

use Closure;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Modules\Acl\app\Models\AclResource;
use Modules\Acl\app\Services\UserService;
use Modules\Form\app\Events\FinalFormElements;
use Modules\Form\app\Events\InitFormElements;
use Modules\Form\app\Services\FormService;
use Modules\SystemBase\app\Http\Livewire\BaseComponent;
use Modules\SystemBase\app\Models\JsonViewResponse;
use Throwable;

class NativeObjectBase extends BaseComponent
{
    use WithFileUploads;

    /**
     * Singular
     *
     * @var string
     */
    #[Computed]
    public string $objectFrontendLabel = 'Object';

    /**
     * Plural
     *
     * @var string
     */
    #[Computed]
    public string $objectsFrontendLabel = 'Objects';

    /**
     * The form is closed by default.
     *
     * @var bool
     */
    public bool $isFormOpen = false;

    /**
     * Determines the ENTER key can accept the form
     *
     * @var bool
     */
    public bool $canKeyEnterSendForm = false;

    /**
     * Enable/disable form actions.
     * Usually changed as a livewire parameter.
     *
     * @var bool
     */
    public bool $actionable = true;

    /**
     * Show form as readonly (background).
     * Usually changed as a livewire parameter.
     *
     * @var bool
     */
    public bool $readonly = false;

    /**
     * True: Try to automatically detect the current user is allowed to edit this object/model.
     * False: Own logic is implemented
     *
     * @var bool
     */
    public bool $autoGuard = true;

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
     * The data as an array (for example, from the db model) resulted from $this->dataSource
     * which is synced with frontend and input elements.
     * Names of form elements if input prefixes with 'dataTransfer.xxx'
     *
     * @var array
     */
    public array $dataTransfer = [];

    /**
     * Saved credential you never want to place in json output.
     * Should automatically be filled (by Model $hidden or whatever)
     * "Protected" Computed property excluded from JSON payload.
     *
     * @var array
     */
    #[Computed]
    protected array $dataCredentials = [];

    /**
     * Metadata like validation types for specific form input elements
     *
     * @var array|array[]
     */
    protected array $elementMetaData = [];

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
     * @var array
     */
    public array $activeTabs = [];

    /**
     * Determine whether the frontend form elements should display
     * inline placeholders or the label.
     *
     * @var bool
     */
    public bool $useElementPlaceholders = true;

    /**
     * @var array|string[]
     */
    public array $formActionButtons = [
        'cancel' => 'form::components.form.actions.defaults.cancel',
        'delete' => 'form::components.form.actions.defaults.delete',
        'accept' => 'form::components.form.actions.defaults.accept',
    ];

    /**
     * if true adding: x-data="{form_data:$wire.dataTransfer}" to form
     * This is not needed in general, $wire.xxx will update by js instantly
     *
     * @var bool
     */
    public bool $autoXData = false;

    const int switch3No = -100;
    const int switch3Unused = 0;
    const int switch3Yes = 100;

    /**
     * Can be overwritten by livewire form components to prepare the element views.
     *
     * @todo: 'default' is questionable especially if value is false, maybe remove 'default' this way
     * @todo: defaults should set by overriding makeObjectInstanceDefaultValues()
     *
     * @var array
     */
    const array defaultViewData = [
        'auto_complete'     => true,
        'css_classes'       => '',
        'css_group'         => '',
        'default'           => '',
        'description'       => '',
        'disabled'          => false,
        'dusk'              => null,
        'element_index'     => 0,
        'html_data'         => [],
        'icon'              => null,
        'id'                => '',
        'label'             => '',
        'label_limit'       => 40,
        'livewire'          => '',
        'livewire_click'    => '',
        'livewire_live'     => false,
        'livewire_debounce' => 750, // (in ms, but without suffix 'ms' here) only used when livewire_live=true
        'name'              => '',
        'options'           => [],
        'read_only'         => false,
        'type'              => 'text',
        'value'             => '',
        'visible'           => true,
        'x_data'            => [],
        'x_model'           => null,
    ];

    /**
     * @var array
     */
    #[Computed]
    public array $defaultTabPageData = [
        'css_classes' => '',
        'disabled'    => false,
        'visible'     => true,
    ];

    /**
     * inherited nested form fields in following order:
     * - whole form data array
     * - tab_controls
     * - tabs inside tab_pages
     * - tab content
     * - all form_elements
     *
     * @var array
     */
    #[Computed]
    public array $inheritViewData = [
        'auto_complete' => true,
        'disabled'      => false,
        'livewire'      => '',
        'read_only'     => false,
        'visible'       => true,
        'x_model'       => null,
    ];

    /**
     * Root name for all form elements (like 'root' for result name="root.email")
     *
     * @var string
     */
    public string $formRootName = '';

    /**
     * @var array|string[]
     */
    protected array $forceValidElementFields = [
        '__confirm__password',
    ];

    /**
     * @return void
     */
    protected function initMount(): void
    {
        parent::initMount();

        $this->objectInstanceDefaultValues = $this->makeObjectInstanceDefaultValues();

        InitFormElements::dispatch($this);

        $this->initLiveCommands();
    }

    /**
     * @return void
     */
    protected function initBooted(): void
    {
        parent::initBooted();

        $this->elementMetaData = [
            'number' => [
                'type' => ['integer'],
            ],
            'switch' => [
                'type' => ['bool'],
                'cast' => fn($v) => data_get($this->dataSource, 'value', false),
            ],
            'select' => [
                'type' => ['array', 'nullable'],
            ],
        ];

        $this->objectInstanceDefaultValues = $this->makeObjectInstanceDefaultValues();

        InitFormElements::dispatch($this);
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
     * Can be overwritten, but shouldn't be needed, because it will return the proper model instance.
     * If id is invalid, an empty object will be returned.
     *
     *
     * @return mixed
     */
    #[Computed(persist: false)]
    public function dataSource(): mixed
    {
        return $this->loadDataSource($this->formObjectId);
    }

    /**
     * Overwrite to fill computed dataSource.
     *
     * @param $id
     *
     * @return mixed
     */
    public function loadDataSource($id): mixed
    {
        return [];
    }

    /**
     * the updating process used for all liveCommands
     *
     * @param $property
     * @param $value
     *
     * @return void
     */
    #[On('updating')]
    public function updating($property, $value): void
    {
        $this->liveCommandsUpdating($property, $value);
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
     * Destroy all relevant data from previous opened form.
     * Should be overwritten if there is more to reset.
     *
     * @return void
     */
    public function resetFormRelevantData(): void
    {
        $this->reset(['isFormOpen']);
        $this->reset(['dataTransfer']);
        unset($this->dataSource);
        unset($this->finalFormElements);
        unset($this->formHtml);
    }

    /**
     * Overwrite if needed.
     *
     * @return void
     */
    protected function beforeRender(): void
    {
        // test to find infinity loop by dispatch()->to( parentFormX )
        // + found uniqid() in tabsControls
        //
        //Log::debug(print_r([
        //    'id'                 => $this->id(),
        //    'isFormOpen'         => $this->isFormOpen,
        //    'formObjectId'       => $this->formObjectId,
        //    //'finalFormElements'  => count($this->finalFormElements),
        //    //'dataTransfer (ser)' => strlen(serialize($this->dataTransfer)),
        //    //'dataSource (ser)'   => strlen(serialize($this->dataSource)),
        //    'dataTransfer (md5)' => md5(serialize($this->dataTransfer)),
        //    'dataSource (md5)' => md5(serialize($this->dataSource)),
        //    'formHtml (md5)' => md5($this->formHtml),
        //    'formHtml' => serialize($this->formHtml),
        //    'finalFormElements (md5)' => md5(serialize($this->finalFormElements)),
        //], true));
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
     * Shown while this component is lazy loaded (see 'lazy' in the element-dt-* form elements).
     *
     * @param  array  $params
     *
     * @return string
     */
    public function placeholder(array $params = []): string
    {
        return '<div class="min-h-24 flex items-center justify-center text-sm text-secondary-500"><span class="animate-pulse">'
            .e(__('Loading ...')).'</span></div>';
    }

    /**
     * Calculating and generating the form object,
     * prepare dataTransfer
     * and adjust actionable and readonly flags depends on form config
     *
     * @return mixed
     * @throws Throwable
     * @todo: replace and resolve
     */
    protected function generateFormElements(): mixed
    {
        if (!$this->isFormOpen) {
            return [];
        }

        // after form calculation, adjust permissions
        $this->readonly = !$this->canEdit();
        $this->actionable = $this->canEdit();

        // Important to check if $this->dataTransfer was already filled!
        if (!$this->dataTransfer) {
            $this->refreshTransferData();
        }

        return $this->dataSource;
    }

    /**
     * create or renew dataTransfer based on dataSource
     *
     * @return void
     */
    public function refreshTransferData(): void
    {
        if ($object = $this->dataSource) {
            $newData = app('system_base')->toArrayPreserveKeys($object); // take all but respect hidden
        } else {
            $newData = [];
        }

        //$this->trySetDataTransferValue('', $newData);
        $this->dataTransfer = $newData;

        //// dataCredentials is protected and not part of payload, so its secure
        //// but actually we don't need this
        //foreach ($this->dataSource->gethidden() as $key) {
        //    $this->dataCredentials[$key] = $this->dataSource->$key;
        //}
    }

    /**
     * @return bool
     */
    protected function isFormCreated(): bool
    {
        return (bool) $this->dataSource;
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

    /**
     * Should be overwritten.
     *
     * @return bool
     */
    protected function afterOpenForm(): bool
    {
        // call js function formOpened() ...
        //$this->dispatch('formOpened');

        return true;
    }

    /**
     * @param  mixed  $id  can also be an array (i.e.: ratings)
     * @param  bool   $forceReset
     *
     * @return void
     * @throws Throwable
     */
    #[On('open-form')]
    public function openForm(mixed $id, bool $forceReset = true): void
    {
        if ($forceReset) {
            // completely destroy data from old form ...
            $this->resetFormRelevantData();
        }

        $this->isFormOpen = true;
        $this->formObjectId = $id;

        // calculate and generate form
        if (!$this->generateFormElements()) {
            Log::warning('Failed to generate form result for form id '.$id, [__METHOD__]);
        }

        // event
        //$this->afterOpenForm();

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
            $this->dispatch('refresh')
                ->to($this->relatedLivewireDataTable);
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
        $jsonResponse = new JsonViewResponse();
        if ($this->validateForm()) {

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
            // dataSource is a computed property and was already loaded in this request
            // (before saving, e.g. by validateForm()). Drop it, otherwise dataTransfer
            // gets the old values (like extra attributes) and the form shows outdated data.
            unset($this->dataSource);
            unset($this->finalFormElements);
            unset($this->formHtml);
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
        // Model have to exists ...
        if ($this->dataSource) {

            try {

                $jsonResponse = new JsonViewResponse();
                $validatedData = $this->validateFormData($this->dataTransfer, $jsonResponse);
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


    /**
     * Complex validation of data (from frontend json mostly)
     *
     * @param  array             $data
     * @param  JsonViewResponse  $jsonResponse
     * @param  array             $additionalValidateFormat
     *
     * @return array
     * @throws ValidationException
     */
    public function validateFormData(array $data, JsonViewResponse $jsonResponse, array $additionalValidateFormat = []): array
    {
        $validated = [];
        $validatorPrefix = '';
        if ($this->formRootName) {
            $validatorPrefix = $this->formRootName.'.';
        }

        // Get the validate FORMAT for each element ...
        $validateFormat = $this->runValidateFormElements(function ($formElement, $key) use ($validatorPrefix, &$data, $jsonResponse) {
            $validateData = [];

            // @todo: check for uniques like email ...

            // correct key if needed
            $key = $this->getElementName($formElement, $key);

            // Check for select relations with value 'No choice'.
            // Checking 'select' is not enough, so we will check options contains the no choice key ...
            $keyNoChoice = app('system_base')::selectValueNoChoice;
            if (Arr::has($formElement, 'options.'.$keyNoChoice)) {
                if (data_get($data, $key, '') == $keyNoChoice) {
                    $data[$key] = null;
                }
            }

            // Validate date
            if (data_get($formElement, 'html_element') === 'date') {
                // If date is not set force it to null
                if (!data_get($data, $key)) {
                    $data[$key] = null;
                }
            }

            // Check for passwords.
            // If so, then:
            // 1) check for existing confirmation
            // 2) compare confirmation
            // 3) remove confirmation from data
            if (data_get($formElement, 'html_element') === 'password') {

                // @todo: also check current password? If yes in future, dont need for admins!

                // Currently checking a valid password field and not the confirmation one?
                if (!str_contains($key, '__confirm__')) {

                    $confirmPasswordKey = '__confirm__'.$key;
                    $password = data_get($data, $key, '');
                    $confirmPassword = data_get($data, $confirmPasswordKey, '');

                    if ($password && $confirmPassword) {

                        if ($password === $confirmPassword) {

                            // no need to hash the password, its done implicit by user $casts
                            $data[$key] = $password;

                            // delete the confirmation
                            unset($data[$confirmPasswordKey]);

                        } else {
                            // error, not equal ...
                            $jsonResponse->setErrorMessage(__("Password is not equal the confirmation."));

                            return $validateData;
                        }

                    } else {

                        // Password field was set, but not the confirmation ...
                        if ($password) {
                            $jsonResponse->setErrorMessage(__("Missing the confirmation password."));

                            return $validateData;
                        }
                    }

                }
            }

            if ($v1 = data_get($formElement, 'validator')) {
                if (app('system_base')->isCallableClosure($v1)) {
                    $v1 = $v1($data);
                }
                $validateData[$validatorPrefix.$key] = $v1;
            }

            if ($vMulti = data_get($formElement, 'validator_multi')) {
                foreach ($vMulti as $k2 => $v2) {
                    if (app('system_base')->isCallableClosure($v2)) {
                        $v2 = $v2($data);
                    }
                    $validateData[$validatorPrefix.$k2] = $v2;
                }
            }

            return $validateData;
        });

        if ($jsonResponse->hasErrors()) {
            return $validated;
        }

        $validateFormat = app('system_base')->arrayMergeRecursiveDistinct($additionalValidateFormat, $validateFormat);

        //
        $data = $this->prepareDataForFormattedProperties($data);

        // @todo: send messages to livewire component (over jsonResponse?)
        $validator = Validator::make($data, $validateFormat);

        if ($validator->fails()) {

            // format incoming array properly
            $properValidatorMessages = [];
            foreach (
                $validator->messages()
                    ->getMessages() as $err1Key => $err1Values
            ) {
                foreach ($err1Values as $err1Value) {
                    $properValidatorMessages[] = $err1Key.': '.$err1Value;
                }
            }

            $jsonResponse->setErrorMessage(__('validation.failed'));
            Log::info("Validator error. Remove validator property in form fields, if no validation is required.");
            Log::info("Validator error messages", $properValidatorMessages);
            $jsonResponse->addMessagesToErrorList($properValidatorMessages);
        } else {
            $validated = $validator->validated();
        }

        return $validated;
    }

    /**
     * Loop all nested form elements.
     *
     * @param  callable    $callBackElement
     * @param  array|null  $formElementsRoot  null for root
     *
     * @return array
     */
    public function runValidateFormElements(callable $callBackElement, ?array $formElementsRoot = null): array
    {
        $validateData = [];
        if ($formElementsRoot === null) {
            $formElementsRoot = $this->finalFormElements;
        }

        foreach (data_get($formElementsRoot, 'tab_controls', []) as $tabControlData) {
            foreach (data_get($tabControlData, 'tab_pages', []) as $tabPage) {
                if ($formData = data_get($tabPage, 'content')) {
                    $subValidateData = $this->runValidateFormElements($callBackElement, $formData);
                    $validateData = app('system_base')->arrayMergeRecursiveDistinct($validateData, $subValidateData);
                }
            }
        }

        foreach (data_get($formElementsRoot, 'form_elements', []) as $key => $formElement) {
            $subValidateData = $callBackElement($formElement, $key);
            $validateData = app('system_base')->arrayMergeRecursiveDistinct($validateData, $subValidateData);
        }

        return $validateData;
    }

    /**
     * @return JsonViewResponse
     * @throws ValidationException
     */
    public function runUpdateList(array $items): JsonViewResponse
    {
        $jsonResponse = new JsonViewResponse(__(":name updated.", ['name' => __($this->objectFrontendLabel)]));
        $successData = [
            'created' => [],
            'updated' => [],
        ];
        foreach ($items as $item) {

            // object data have to present in 'data'
            $itemData = data_get($item, 'data');

            // Validate now
            $itemData = $this->validateFormData($itemData, $jsonResponse);
            if (!$itemData || $jsonResponse->hasErrors()) {
                return $jsonResponse;
            }

            // cleanup data to saving poor main object
            $cleanData = $this->getCleanObjectDataForSaving($itemData);

            //
            $this->onAfterUpdateItem($itemData, $jsonResponse, $cleanData);
        }

        $jsonResponse->setData($successData);

        return $jsonResponse;
    }

    /**
     * @param  array  $itemData
     *
     * @return array
     */
    public function getCleanObjectDataForSaving(array $itemData): array
    {
        return $itemData;
    }

    /**
     * @param  array             $itemData
     * @param  JsonViewResponse  $jsonResponse
     *
     * @return false[]
     */
    public function updateItem(array $itemData, JsonViewResponse $jsonResponse): array
    {
        return [
            'updated' => false,
            'created' => false,
        ];
    }

    /**
     * Event after object was saved.
     * Should be called to update relations and stuff.
     *
     * @TODO: 'id' should be dynamically
     *
     * @param  array             $itemData
     * @param  JsonViewResponse  $jsonResponse
     * @param  mixed             $objectInstance
     *
     * @return bool
     */
    public function onAfterUpdateItem(array $itemData, JsonViewResponse $jsonResponse, mixed $objectInstance): bool
    {
        return true;
    }

    /**
     * Event before object was saved.
     * Should be called to preset stuff.
     *
     * @param  array             $itemData
     * @param  JsonViewResponse  $jsonResponse
     * @param  mixed             $objectInstance
     *
     * @return bool
     */
    public function onBeforeUpdateItem(array $itemData, JsonViewResponse $jsonResponse, mixed $objectInstance): bool
    {
        return true;
    }

    /**
     * Event kurz vor dem sync.
     *
     * @param $syncList
     *
     * @return array
     */
    protected function beforeSync($syncList): array
    {
        return $syncList;
    }

    /**
     * Prepare the data like name, value, ... for the view.
     *
     * @param  string        $element
     * @param  string        $name
     * @param  array         $options
     * @param  array         $parentOptions
     * @param  Closure|null  $callbackExtraValidate
     * @param  Closure|null  $callbackTransformValue
     *
     * @return array
     */
    public function prepareFormViewData(string $element, string $name, array $options = [], array $parentOptions = [], ?Closure $callbackExtraValidate = null, ?Closure $callbackTransformValue = null): array
    {
        $parentName = data_get($parentOptions, 'name', '');

        // first fill default values
        $viewData = static::defaultViewData;

        // adjust some special cases ...
        if ($element == 'multi_select') {
            $viewData['value'] = [];
        }

        // merge/inherit parent data
        if ($parentOptions) {
            //$viewData = app('system_base')->arrayMergeRecursiveDistinct($viewData, $parentOptions);
            $viewData = app('system_base')->arrayRootCopyWhitelistedNoArrays($viewData, $parentOptions, $this->inheritViewData);
        }

        // merge/inherit current data
        $viewData = app('system_base')->arrayMergeRecursiveDistinct($viewData, $options);

        /**
         * get name by (first given wins)
         * 1) field from viewData['name']
         * 2) field from viewData['property']
         * 3) take parameter $name
         * 4) add parent name to path if given
         */
        //$name = data_get($viewData, 'name') ?: data_get($viewData, 'property') ?: $name;
        $name = $this->getElementName($viewData, $name);
        $name = ($parentName && $name) ? ($parentName.'.'.$name) : $name;

        // extra validation callback
        if ($callbackExtraValidate && !$callbackExtraValidate($name)) {
            return $viewData;
        }

        //
        $resourcePrevValue = data_get($this->dataSource, $name);

        // assign default if not explicit set in viewData
        if ((!Arr::has($options, 'default')) && Arr::has($this->objectInstanceDefaultValues, $name)) {
            $viewData['default'] = data_get($this->objectInstanceDefaultValues, $name);
        }

        /**
         * get value by (first given wins)
         * 1) direct set by form field viewData['value']
         * 2) from dataSource (if not null)
         * 3) from field viewData['default']
         *
         * @todo : point 3 is questionable especially if value is false, maybe remove 'default' this way
         * @fixed: overwritten null wich was needed
         */
        $value = data_get($viewData, 'value') ?: $resourcePrevValue;
        if (!$value && ($default = data_get($viewData, 'default', ''))) {
            $value = $default;
        }

        // value transform callback
        if ($callbackTransformValue) {
            $value = $callbackTransformValue($name, $value);
        }

        // set calculated values for blade templates
        $viewData['value'] = $value; // ?? '';
        $viewData['name'] = $name;

        //
        $this->calculateCallableValues($viewData);

        return $viewData;
    }

    /**
     * @param  array  $viewData
     *
     * @return void
     */
    protected function calculateCallableValues(array &$viewData): void
    {
        /**
         * check all properties for callables and call it if needed
         */
        foreach ($viewData as $k3 => $v3) {
            if (app('system_base')->isCallableClosure($v3)) {
                $viewData[$k3] = $v3();
            }
        }
    }

    /**
     * Should be overwritten to decide the current object is owned by user
     *
     * @return bool default false
     */
    public function isOwnUser(): bool
    {
        return false;
    }

    /**
     * Should be overwritten to decide the current object is owned by user
     * canEdit() can call canManage() but don't call canEdit() in canManage()!
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        return true;
    }

    /**
     * Should be overwritten to decide the current object is owned by user
     * canEdit() can call canManage() but don't call canEdit() in canManage()!
     *
     * @return bool
     */
    public function canManage(): bool
    {
        /** @var UserService $userService */
        $userService = app(UserService::class);

        return $userService->currentUser()
            ->hasAclResource(AclResource::RES_MANAGE_USERS);
    }

    /**
     * @return array
     */
    protected function getDefaultFormSettingsByPermission(): array
    {
        $canManage = $this->canManage();
        $canEdit = $this->canEdit();
        $disabled = false;

        return [
            'is_owner'   => $this->isOwnUser(),
            'can_manage' => $canManage,
            'can_edit'   => $canEdit,
            'read_only'  => $disabled,
            'disabled'   => $disabled,
            'public'     => true,
            'element_dt' => ($canManage && !$disabled) ? 'element-dt-split-with-form' : ($canEdit ? 'element-dt-selected-with-form' : 'element-dt-selected-no-interaction'),
            //            'element_dt' => 'element-dt-split-with-form',
        ];
    }

    /**
     * try to get the user id related to this object.
     *
     * @param  bool  $canAuthId
     *
     * @return mixed
     */
    public function getOwnerUserId(bool $canAuthId = true): mixed
    {
        // 1) Autodetect: Check whether there is an existing user_id ...
        if ($this->dataSource && data_get($this->dataSource, 'user_id')) {
            return data_get($this->dataSource, 'user_id');
        }

        // 2) Check parent id (should be user object)
        if ($id = data_get($this->parentData, 'id')) {
            // @todo: should be check whether parent is User?
            return $id;
        }

        // 3) Try to get user_id by assigned default values
        return data_get($this->objectInstanceDefaultValues, 'user_id', $canAuthId ? Auth::id() : 0);
    }

    /**
     * $element can declared like "MyModule::textarea" or just "select"
     *
     * @param  string  $element
     *
     * @return array
     */
    public function getElementModuleInfo(string $element): array
    {
        $attributeInputData = explode('::', $element);
        if (count($attributeInputData) < 2) { // without module
            $attributeInputModule = '';
            $attributeInput = $attributeInputData[0];
        } else { // with module
            $attributeInputModule = $attributeInputData[0];
            $attributeInput = $attributeInputData[1];
        }

        return [
            'module'  => $attributeInputModule,
            'element' => $attributeInput,
        ];
    }

    /**
     * Cast dot notations and brackets into an array.
     *
     * @param $data
     *
     * @return mixed
     */
    protected function prepareDataForFormattedProperties($data): mixed
    {
        foreach ($data as $keyOriginal => $valueOriginal) {

            // Brackets to dots ...
            $keyModified = str_replace(['][', '[',], '.', $keyOriginal);
            $keyModified = str_replace([']'], '', $keyModified);

            // ... dots to array ...
            if (str_contains($keyModified, '.')) {
                data_fill($data, $keyModified, $valueOriginal);
                unset($data[$keyOriginal]);
            }

        }

        return $data;
    }

    /**
     * Loop all nested form elements.
     *
     * @param  callable     $callBackElement   callback params ($currentTabControl, $currentTabPage, $key, $currentFullPath)
     * @param  array|null   $formElementsRoot  null for root
     * @param  string|null  $currentTabControl
     * @param  string|null  $currentTabPage
     * @param  string       $currentFullPath
     *
     * @return void
     */
    public function runAllFormElements(callable $callBackElement, ?array $formElementsRoot = null, ?string $currentTabControl = null, ?string $currentTabPage = null, string $currentFullPath = ''): void
    {
        if ($formElementsRoot === null) {
            $formElementsRoot = $this->finalFormElements;
        }

        foreach (data_get($formElementsRoot, 'tab_controls', []) as $tabControlKey => $tabControlData) {
            foreach (data_get($tabControlData, 'tab_pages', []) as $tabPageKey => $tabPage) {
                if ($formData = data_get($tabPage, 'content')) {
                    $p = $currentFullPath.(($currentFullPath) ? '.' : '').'tab_controls.'.$tabControlKey.'.tab_pages.'.$tabPageKey.'.content';
                    $this->runAllFormElements($callBackElement, $formData, $tabControlKey, $tabPageKey, $p);
                }
            }
        }

        foreach (data_get($formElementsRoot, 'form_elements', []) as $key => $formElement) {
            $callBackElement($currentTabControl, $currentTabPage, $key, $currentFullPath.(($currentFullPath) ? '.' : '').'form_elements.'.$key);
        }
    }

    /**
     * Overwrite this method to define your form.
     * Call this method as parent::getFormElements()
     *
     * @return array
     */
    public function getFormElements(): array
    {
        return [
            'css_classes' => 'form-edit',
            // dataTransfer is the container property we want sync with livewire
            'livewire'    => 'dataTransfer',
            'title'       => $this->makeFormTitle($this->dataSource, 'id'),
        ];
    }

    /**
     * @return array
     */
    #[computed]
    public function finalFormElements(): array
    {
        if ($this->autoGuard && !$this->canEdit()) {
            return [];
        }

        $result = $this->getFormElements();

        if (isset($result['tab_controls'])) {
            // inherit tab pages defaults ...
            foreach ($result['tab_controls'] as &$tabControlData) {
                foreach ($tabControlData['tab_pages'] as &$tabPage) {
                    if (empty($tabPage['tab'])) {
                        continue;
                    }
                    //$defaultData = $this->defaultTabPageData; // just a copy for the next step
                    $tabPage = app('system_base')->arrayMergeRecursiveDistinct($this->defaultTabPageData, $tabPage);
                }
            }
        }

        // fire event for modules
        FinalFormElements::dispatch($this);

        return $result;
    }

    /**
     * Cached formHtml
     * Use this late (in blade)
     *
     * @return string
     * @throws Throwable
     */
    #[computed]
    public function formHtml(): string
    {
        return $this->renderElement('full_form', '', $this->finalFormElements);
    }

    /**
     * @param  mixed   $dataSource
     * @param  string  $displayKey
     *
     * @return string
     */
    protected function makeFormTitle(mixed $dataSource, string $displayKey): string
    {
        if ($dataSource) {
            $result = sprintf(__("Change %s: %s"), __($this->objectFrontendLabel), data_get($dataSource, $displayKey, 0));
        } else {
            $result = sprintf(__("Create %s"), __($this->objectFrontendLabel));
        }

        return $result;
    }

    /**
     * get name by (first given wins)
     * 1) field from viewData['name']
     * 2) field from viewData['property']
     * 3) take parameter $name
     * 4) add parent name to path if given
     *
     * @param  array   $elementConfigData
     * @param  string  $default
     *
     * @return string
     */
    protected function getElementName(array $elementConfigData, string $default): string
    {
        if (!$name = data_get($elementConfigData, 'name')) {
            $name = data_get($elementConfigData, 'property') ?: $default;
        }

        //$name = str_replace('-', '_', $name);
        return $name;
    }

    /**
     * @param  string|callable  $element
     * @param  string           $name
     * @param  array            $options
     * @param  array            $parentOptions
     *
     * @return string
     * @throws Throwable
     */
    public function renderElement(string|callable $element, string $name, array $options = [], array $parentOptions = []): string
    {
        if (app('system_base')->isCallableClosure($element)) {
            $element = $element();
        }

        // get the well-formed view data
        $viewData = $this->prepareFormViewData($element, $name, $options, $parentOptions);

        // If not available or not visible, avoid rendering.
        if (!$viewData['visible']) {
            return '';
        }

        // @deprecated 'html_element_module': use "MyModule::some_element" like above
        // if "html_element_module" given, use this ...
        if ($htmlElementModule = data_get($options, 'html_element_module', '')) {
            $viewPath = $htmlElementModule ? $htmlElementModule.'::components.form' : '';
        } else {
            // otherwise check element is declared like "MyModule::some_element" ...
            $ei = $this->getElementModuleInfo($element);
            // if so ...
            if ($ei['module']) {
                $moduleService = app('system_base_module');
                if (!$moduleService->moduleExists($ei['module'])) {
                    return '';
                }

                $element = $ei['element'];
                $viewPath = $ei['module'].'::components.form';
            } else {
                // default view path ...
                $viewPath = 'form::components.form';
            }
        }

        // Render the given element
        return view($viewPath.'.'.$element, ['data' => $viewData, 'form_instance' => $this])->render();
    }

    /**
     * @param  string  $name
     * @param  array   $viewData
     * @param  array   $preparedAttrs
     *
     * @return array
     */
    protected function getInputAttributesForText(string $name, array $viewData, array $preparedAttrs): array
    {
        $_isPassword = (data_get($viewData, 'type') === 'password');
        //$xModelName = ((data_get($viewData, 'x_model')) ? (data_get($viewData, 'x_model').'.'.$name) : '');
        //$livewire = data_get($viewData, 'livewire');

        //$preparedAttrs['class'] = 'form-control '.data_get($preparedAttrs, 'class') . ' text-red-800 placeholder:text-red-700/50';
        $preparedAttrs['class'] = 'form-control';
        if ($this->useElementPlaceholders) {
            $preparedAttrs['placeholder'] = data_get($viewData, 'label');
            $preparedAttrs['class'] .= ' [&::placeholder]:text-info-800 [&::placeholder]:opacity-50';
        }
        $preparedAttrs['class'] .= data_get($preparedAttrs, 'class');

        if ($tmp = data_get($viewData, 'type')) {
            $preparedAttrs['type'] = $tmp;
        }
        //if (!$livewire && !$xModelName) {
        $preparedAttrs['value'] = !$_isPassword ? data_get($viewData, 'value') : '';
        //}
        if (!data_get($viewData, 'auto_complete')) {
            $preparedAttrs['autocomplete'] = $_isPassword ? 'new-password' : 'off';
        }

        return $preparedAttrs;
    }

    /**
     * @param  string  $name
     * @param  array   $viewData
     * @param  array   $preparedAttrs
     *
     * @return array
     */
    protected function getInputAttributesForTextArea(string $name, array $viewData, array $preparedAttrs): array
    {
        $preparedAttrs['class'] = 'form-control field-sizing-content min-h-40';
        if ($this->useElementPlaceholders) {
            $preparedAttrs['placeholder'] = data_get($viewData, 'label');
            $preparedAttrs['class'] .= ' [&::placeholder]:text-info-800 [&::placeholder]:opacity-50';
        }
        $preparedAttrs['class'] .= data_get($preparedAttrs, 'class');

        return $preparedAttrs;
    }

    /**
     * @param  string  $name
     * @param  array   $viewData
     * @param  array   $preparedAttrs
     *
     * @return array
     */
    protected function getInputAttributesForSelect(string $name, array $viewData, array $preparedAttrs): array
    {
        $preparedAttrs['class'] = 'form-select '.data_get($preparedAttrs, 'class');
        if (data_get($viewData, 'multiple')) {
            $preparedAttrs['multiple'] = 'multiple';
            if ($tmp = data_get($viewData, 'list_size', 6)) {
                $preparedAttrs['size'] = $tmp;
            }
        }

        return $preparedAttrs;
    }

    /**
     * @param  string  $name
     * @param  array   $viewData
     * @param  array   $preparedAttrs
     *
     * @return array
     */
    protected function getInputAttributesForCheckbox(string $name, array $viewData, array $preparedAttrs): array
    {
        $preparedAttrs['type'] = data_get($viewData, 'type') ?? 'checkbox';
        $preparedAttrs['class'] = 'form-control form-check-input '.data_get($preparedAttrs, 'class');

        $livewire = data_get($viewData, 'livewire');
        $xModelName = ((data_get($viewData, 'x_model')) ? (data_get($viewData, 'x_model').'.'.$name) : '');
        if (!$livewire && !$xModelName && data_get($viewData, 'value')) {
            $preparedAttrs['checked'] = 'checked';
        }

        return $preparedAttrs;
    }

    /**
     * @param  array  $viewData
     *
     * @return ComponentAttributeBag
     */
    public function calcInputAttributes(array $viewData): ComponentAttributeBag
    {
        $markerWithoutAttribute = '#[NO_ATTRIBUTE]#';
        $xModelName = ((data_get($viewData, 'x_model')) ? (data_get($viewData, 'x_model').'.'.data_get($viewData, 'name')) : '');
        $livewire = data_get($viewData, 'livewire');
        $name = data_get($viewData, 'name');
        $default = data_get($viewData, 'default', '');
        $xClasses = [];
        $htmlElement = data_get($viewData, 'html_element');
        $useSync = true; //($htmlElement !== 'password');

        // ----------------------------------------------
        // Attributes for all kind of elements ...
        // ----------------------------------------------
        $attrs = [
            //'x-init' => '', // at first (maybe not filled below - empty keys will not be rendered)
            'name'  => $name,
            'class' => data_get($viewData, 'css_classes'),
        ];

        if ($tmp = data_get($viewData, 'id')) {
            $attrs['id'] = $tmp;
        }
        if ($useSync && $xModelName) {
            $attrs['x-model'] = $xModelName;
        }

        if ($useSync && $livewire) {
            $wireKey = 'wire:model';
            $wireFrontendName = $livewire.'.'.$name;

            if (data_get($viewData, 'livewire_live')) {
                $wireKey .= '.live';
                // debounce only when livewire_live
                if ($tmp = data_get($viewData, 'livewire_debounce')) {
                    $wireKey .= '.debounce.'.$tmp.'ms';
                }
            }
            $attrs[$wireKey] = $wireFrontendName;

            // alpine check defaults
            if (!in_array($htmlElement, ['hidden', 'password'])) {
                // In js we need a special check if no default.
                // In this case we want a 'falsy' check: null, 0 or empty strings.
                if ($default) {
                    // alpine check default value
                    $xClasses['default-value'] = "(getValue(\$wire, '$attrs[$wireKey]', null)=='$default')";
                } else {
                    // alpine check default value (empty)
                    $xClasses['default-value'] = "isValueEmpty(getValue(\$wire, '$attrs[$wireKey]', null))";
                }
            }

        }

        // finally calc :class / $xClasses
        //$attrs[':class'] = $this->getSpecialJsonForJS($xClasses);

        //
        if (data_get($viewData, 'disabled')) {
            $attrs['disabled'] = 'disabled';
        }
        if (data_get($viewData, 'read_only')) {
            $attrs['readonly'] = $markerWithoutAttribute;
        }
        if ($tmp = data_get($viewData, 'dusk')) {
            $attrs['dusk'] = $tmp;
        }

        // direct defined attributes
        foreach (data_get($viewData, 'attributes', []) as $k => $v) {
            $attrs[$k] = $v;
        }

        // data-.... attributes
        foreach (data_get($viewData, 'html_data', []) as $k => $v) {
            $attrs['data-'.$k] = $v;
        }

        // x-.... attributes
        foreach (data_get($viewData, 'x_data', []) as $k => $v) {
            $attrs['x-'.$k] = $v;
        }

        // ----------------------------------------------
        // Attributes for input text and similar ...
        // ----------------------------------------------
        $htmlInputTypes = ['text', 'hidden', 'password', 'email', 'number', 'number_int', 'date', 'datetime-local']; // blades
        if (in_array($htmlElement, $htmlInputTypes)) {
            $attrs = $this->getInputAttributesForText($name, $viewData, $attrs);
        }

        // ----------------------------------------------
        // Attributes for textarea and similar ...
        // ----------------------------------------------
        $htmlInputTypes = ['textarea']; // blades
        if (in_array($htmlElement, $htmlInputTypes)) {
            $attrs = $this->getInputAttributesForTextArea($name, $viewData, $attrs);
        }

        // ----------------------------------------------
        // Attributes for checkbox ...
        // ----------------------------------------------
        $htmlInputTypes = ['checkbox', 'switch']; // blades
        if (in_array($htmlElement, $htmlInputTypes)) {
            $attrs = $this->getInputAttributesForCheckbox($name, $viewData, $attrs);
        }

        // ----------------------------------------------
        // Attributes for select ...
        // ----------------------------------------------
        $htmlInputTypes = ['select', 'multi_select', 'sortable_multi_select']; // blades
        if (in_array($htmlElement, $htmlInputTypes)) {
            $attrs = $this->getInputAttributesForSelect($name, $viewData, $attrs);
        }

        return new ComponentAttributeBag($attrs);
    }

    /**
     * Make json like json_encode() but can skip quoting values to make work conditions and function
     *
     * @param  array   $arr
     * @param  string  $stringQuoteChar
     *
     * @return string
     */
    public function getSpecialJsonForJS(array $arr, string $stringQuoteChar = "'"): string
    {
        $result = '';
        if ($arr) {
            // json_encode() not working here because values are functions or conditions, but not strings ...
            $json = '';
            foreach ($arr as $k => $v) {
                if ($json) {
                    $json .= ',';
                }
                if ($v === null) {
                    $v = 'null';
                }
                if (is_scalar($v)) {
                    if (is_bool($v)) {
                        $v = $v ? 'true' : 'false';
                    } elseif (is_string($v)) {
                        $v = $stringQuoteChar.$v.$stringQuoteChar;
                    }
                } else {
                    $v = $this->getSpecialJsonForJS($v, $stringQuoteChar);
                }
                $json .= $stringQuoteChar.$k.$stringQuoteChar.":".$v;
            }
            $result = '{'.$json.'}';
        }

        return $result;
    }

    /**
     * @param  array   $target
     * @param  string  $path
     * @param  mixed   $value
     *
     * @return bool
     */
    public function setArrayValueIfChanged(array &$target, string $path, mixed $value): bool
    {
        if (app('system_base')->setArrayValueIfChanged($target, $path, $value)) {

            return true;
        }

        return false;
    }

    /**
     * @param  string  $path
     * @param  mixed   $value
     *
     * @return bool
     */
    protected function trySetDataTransferValue(string $path, mixed $value): bool
    {
        return $this->setArrayValueIfChanged($this->dataTransfer, $path, $value);
    }

    // -------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------

    const int viewModeSimple = 500;
    const int viewModeDefault = 1000;
    const int viewModeExtended = 9000;

    /**
     * defaults for $this->liveCommandsConfig
     */
    const array liveFilterConfigDefaults = [
        'reload'      => false,
        'default'     => null,
        'view'        => null,
        'view_params' => [],
    ];

    /**
     * Nested assoc array of elements updated by livewire updating().
     * This will be used to control the form like switch a mode or something.
     * Values will also be saved in session separated by form name.
     * Names of form elements if input will prefix with 'liveCommands.xxx.yyy'
     *
     * @var array|string[]
     */
    public array $liveCommands = [];

    /**
     * additional data for liveCommands.
     * Possible keys see liveFilterConfigDefaults.
     *
     * @var array
     */
    protected array $liveCommandsConfig = [];

    /**
     * Overwrite this to declare form specific liveCommands
     *
     * @return void
     */
    protected function initLiveCommands(): void
    {
    }

    /**
     * @param  string  $suffixKey
     *
     * @return string
     */
    protected function getLiveCommandsSessionFullKey(string $suffixKey): string
    {
        return 'form.'.$this->getName().'.liveCommands.'.$suffixKey;
    }

    /**
     * @param  string      $suffixKey
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    protected function getLiveCommandsSession(string $suffixKey, mixed $default = null): mixed
    {
        return Session::get($this->getLiveCommandsSessionFullKey($suffixKey), $default);
    }

    /**
     * @param  string  $suffixKey
     * @param  mixed   $value
     *
     * @return void
     */
    protected function setLiveCommandsSession(string $suffixKey, mixed $value): void
    {
        Session::put($this->getLiveCommandsSessionFullKey($suffixKey), $value);
    }

    /**
     * @param  string  $suffixKey
     *
     * @return void
     */
    protected function forgetLiveCommandsSession(string $suffixKey): void
    {
        Session::forget($this->getLiveCommandsSessionFullKey($suffixKey));
    }

    /**
     * the updating process used for all liveCommands
     *
     * @param $property
     * @param $value
     *
     * @return void
     */
    public function liveCommandsUpdating($property, $value): void
    {
        $propertyPrepared = Str::chopStart($property, 'liveCommands.');
        if (Arr::has($this->liveCommands, $propertyPrepared)) {
            $this->setLiveCommand($propertyPrepared, $value);

            // reload is for store switches for example
            $reload = data_get($this->liveCommandsConfig, $propertyPrepared.'.reload', false);

            // reopen ...
            $this->reopenFormIfNeeded($reload); // true is important to reload all values!
            Log::warning("Reload?", [$reload, __METHOD__]);
        }
    }

    /**
     * @param  int  $default
     *
     * @return void
     */
    protected function addViewModeCommand(int $default = self::viewModeDefault): void
    {
        /** @var FormService $formService */
        $formService = app(FormService::class);

        $key = 'controls_set_view_mode';
        $config = [
            'reload'      => false,
            'default'     => $default,
            'view'        => 'form::components.form.select',
            'view_params' => $formService::getFormElementFormViewMode(),
        ];
        $this->initLiveCommand($key, $config);
    }

    /**
     * @return void
     */
    protected function addReloadCommand(): void
    {
        $key = 'controls_reload';
        $config = [
            'reload' => true, // that's the only task of this button
            'view'   => 'form::components.form.reload',
        ];
        $this->initLiveCommand($key, $config);
    }

    /**
     * @param  int  $atLeast
     *
     * @return bool
     */
    public function viewModeAtLeast(int $atLeast = self::viewModeDefault): bool
    {
        return data_get($this->liveCommands, 'controls_set_view_mode', self::viewModeDefault) >= $atLeast;
    }

    /**
     * @param  int  $atMax
     *
     * @return bool
     */
    public function viewModeAtMaximum(int $atMax = self::viewModeDefault): bool
    {
        return data_get($this->liveCommands, 'controls_set_view_mode', self::viewModeDefault) <= $atMax;
    }

    /**
     * Set the liveFilter value and update the session.
     *
     * @param  string  $propertyPrepared
     * @param  mixed   $value
     *
     * @return void
     */
    protected function setLiveCommand(string $propertyPrepared, mixed $value): void
    {
        if ($this->setArrayValueIfChanged($this->liveCommands, $propertyPrepared, $value)) {

            // Also update session for this form type ...
            $this->setLiveCommandsSession($propertyPrepared, $value);
        }
    }

    /**
     * @param  string      $propertyPrepared
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    protected function getLiveCommand(string $propertyPrepared, mixed $default = null): mixed
    {
        return data_get($this->liveCommands, $propertyPrepared, $default);
    }

    /**
     * @param  string  $propertyPrepared
     *
     * @return bool
     */
    protected function hasLiveCommand(string $propertyPrepared): bool
    {
        return Arr::has($this->liveCommands, $propertyPrepared);
    }

    /**
     * @return bool
     */
    protected function hasLiveCommands(): bool
    {
        return !!$this->liveCommands;
    }

    /**
     * @param  string  $propertyPrepared
     * @param  array   $config
     *
     * @return void
     */
    protected function initLiveCommand(string $propertyPrepared, array $config = []): void
    {
        $x = self::liveFilterConfigDefaults;
        $this->liveCommandsConfig[$propertyPrepared] = app('system_base')->arrayMergeRecursiveDistinct($x, $config);

        data_set($this->liveCommandsConfig, $propertyPrepared.'.reload', data_get($config, 'reload'));
        // Use session if exists. Otherwise, use a default.
        $v = (int) $this->getLiveCommandsSession($propertyPrepared, data_get($config, 'default'));
        $this->setLiveCommand($propertyPrepared, $v);
    }

}
