<?php

namespace Modules\Form\app\Forms\Base;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Acl\app\Models\AclResource;
use Modules\Acl\app\Services\UserService;
use Modules\Form\app\Events\BeforeRenderForm;
use Modules\Form\app\Events\FinalFormElements;
use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;
use Modules\SystemBase\app\Models\JsonViewResponse;

/**
 * Form base class for all kinds of objects.
 *
 * The different to the livewire class (Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase) is
 * this class handles all html structure, layout, design
 * while the livewire will handle all data and their sync of backend and frontend
 *
 */
class NativeObjectBase
{
    const int switch3No = -100;
    const int switch3Unused = 0;
    const int switch3Yes = 100;

    /**
     * @var NativeObjectBaseLivewire|null
     */
    public ?NativeObjectBaseLivewire $formLivewire = null;

    /**
     * Can be overwritten by livewire form components to prepare the element views.
     *
     * @todo: 'default' is questionable especially if value is false, maybe remove 'default' this way
     * @todo: defaults should set by overriding makeObjectInstanceDefaultValues()
     *
     * @var array
     */
    public array $defaultViewData = [
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
        'livewire_live'     => false,
        'livewire_debounce' => 750, // (in ms, but without suffix 'ms' here) only used when livewire_live=true
        'name'              => '',
        'options'           => [],
        'read_only'         => false,
        'value'             => '',
        'visible'           => true,
        'x_data'            => [],
        'x_model'           => null,
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
    public array $inheritViewData = [
        'auto_complete' => true,
        'disabled'      => false,
        'livewire'      => '',
        'read_only'     => false,
        'visible'       => true,
        'x_model'       => null,
    ];

    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'Object';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Objects';

    /**
     * Root name for all form elements (like 'root' for result name="root.email")
     *
     * @var string
     */
    public string $formRootName = '';

    /**
     * Set for example 'web_uri' or 'shared_id' to try load from this property if is not numeric in initDataSource().
     * Model have to be trait by TraitBaseModel to become loadByFrontEnd()
     *
     * @var string
     */
    public const string frontendKey = '';

    /**
     * @var array|string[]
     */
    protected array $forceValidElementFields = [
        '__confirm__password',
    ];

    /**
     * cached form elements
     * use getFinalFormElements()
     *
     * @var array
     */
    private array $finalFormElements = [];

    /**
     * @return JsonResource|null
     */
    public function getDataSource(): ?JsonResource
    {
        return $this->formLivewire->getDataSource();
    }

    /**
     * @param  JsonResource|null  $dataSource
     *
     * @return void
     */
    public function setDataSource(?JsonResource $dataSource): void
    {
        $this->formLivewire->setDataSource($dataSource);
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
        return $this->formLivewire->initDataSource($id);
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
        return $this->formLivewire->makeObjectInstanceDefaultValues();
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
     * Complex validation of data (from frontend json mostly)
     *
     * @param  array             $data
     * @param  JsonViewResponse  $jsonResponse
     * @param  array             $additionalValidateFormat
     *
     * @return array
     * @throws ValidationException
     */
    public function validate(array $data, JsonViewResponse $jsonResponse, array $additionalValidateFormat = []): array
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

            // Check for select relations with value 'No choice'
            // @todo: not only 'select' are select fields! Also check all other select elements!
            if (data_get($formElement, 'html_element') === 'select') {
                // Do not use === to comparing selectValueNoChoice!
                if (data_get($data, $key, '') == app('system_base')::selectValueNoChoice) {
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
            foreach ($validator->messages()->getMessages() as $err1Key => $err1Values) {
                foreach ($err1Values as $err1Value) {
                    $properValidatorMessages[] = $err1Key.': '.$err1Value;
                }
            }

            $jsonResponse->setErrorMessage(__('validation.failed'));
            Log::info("Validator error. Remove validator property in form fields, if no validation is required.");
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
            $this->initDataSource(); // force reload if possible or get a blank one
            $formElementsRoot = $this->getFinalFormElements();
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
            $formElementsRoot = $this->getFinalFormElements();
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
            'title'       => $this->makeFormTitle($this->getDataSource(), 'id'),
        ];
    }

    /**
     * get defined form elements and cache them as finalFormElements
     *
     * @return array
     */
    public function getFinalFormElements(): array
    {
        if (!$this->finalFormElements) {
            $this->finalFormElements = $this->getFormElements();

            // fire event for modules
            FinalFormElements::dispatch($this);
        }

        return $this->finalFormElements;
    }

    /**
     * @param $id
     *
     * @return JsonResource
     */
    public function renderWithResource(mixed $id = null): JsonResource
    {
        $resource = $this->initDataSource($id);

        // prepare it once before dispatch event
        $this->getFinalFormElements();

        // fire event for modules
        BeforeRenderForm::dispatch($this);

        $html = $this->renderElement('full_form', '', $this->getFinalFormElements());

        // additional is part of JsonResource to add custom metadata
        $resource->additional = [
            'form_html'           => $html,
            'final_form_elements' => $this->getFinalFormElements(),
        ];

        return $resource;
    }

    /**
     * @param  array  $items
     *
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
            $itemData = $this->validate($itemData, $jsonResponse);
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
     * @param  JsonResource|null  $dataSource
     * @param  string             $displayKey
     *
     * @return string
     */
    protected function makeFormTitle(?JsonResource $dataSource, string $displayKey): string
    {
        if ($dataSource) {
            $result = sprintf(__("Change %s: %s"), __($this->objectFrontendLabel), data_get($dataSource, $displayKey, 0));
        } else {
            $result = sprintf(__("Create %s"), __($this->objectFrontendLabel));
        }

        return $result;
    }

    /**
     * Check whether value is a $cast attribute we have to transform it before view (like json)
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return false|mixed|string
     */
    protected function checkViewDataCastAttributeValue(string $name, mixed $value): mixed
    {
        if ($this->getDataSource()->hasCast($name, ['array', 'object'])) {

            // JSON_UNESCAPED_SLASHES to fix "\/" in textarea
            $jsonEncodeFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;

            // only array and objects here ...
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, $jsonEncodeFlags);
            } elseif (is_string($value) && ($value !== '')) { // something was wrong with cast if it's a non-decoded string ...
                $value = json_encode(json_decode($value, true), $jsonEncodeFlags);
            }

        }

        return $value;
    }

    /**
     * Prepare the data like name, value, ... for the view.
     *
     * @param  string  $element
     * @param  string  $name
     * @param  array   $options
     * @param  array   $parentOptions
     *
     * @return array
     */
    public function prepareFormViewData(string $element, string $name, array $options = [], array $parentOptions = []): array
    {
        $parentName = data_get($parentOptions, 'name', '');

        // first fill default values
        $viewData = $this->defaultViewData;

        // adjust some special cases ...
        if ($element == 'multi_select') {
            $viewData['value'] = [];
        }

        // merge/inherit parent data
        if ($parentOptions) {
            // @todo: why is arrayCopyWhitelisted() not enough?
            //$viewData = app('system_base')->arrayMergeRecursiveDistinct($viewData, $parentOptions);
            $viewData = app('system_base')->arrayRootCopyWhitelistedNoArrays($viewData,
                $parentOptions,
                $this->inheritViewData);
        }

        // merge/inherit current data
        // @todo: why is arrayCopyWhitelisted() not enough?
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

        //
        $resourcePrevValue = data_get($this->getDataSource(), $name);

        /**
         * get value by (first given wins)
         * 1) direct set by form field viewData['value']
         * 2) from dataSource (if not null)
         * 3) from field viewData['default']
         *
         * @todo: point 3 is questionable especially if value is false, maybe remove 'default' this way
         */
        $value = data_get($viewData, 'value') ?: $resourcePrevValue ?? data_get($viewData, 'default', '');

        // set calculated values for blade templates
        $viewData['value'] = $value ?? '';
        $viewData['name'] = $name;

        //
        $this->calculateCallableValues($viewData);

        return $viewData;
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
        return data_get($elementConfigData, 'name') ?: data_get($elementConfigData, 'property') ?: $default;
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
     * @param  string|callable  $element
     * @param  string           $name
     * @param  array            $options
     * @param  array            $parentOptions
     *
     * @return string
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

        $viewData['form_instance'] = $this;
        $viewData['form_livewire'] = $this->formLivewire;
        $viewData['object'] = $this->getDataSource();

        // @deprecated 'html_element_module': use "MyModule::some_element" like above
        // if "html_element_module" given, use this ...
        if ($htmlElementModule = data_get($options, 'html_element_module', '')) {
            $viewPath = $htmlElementModule ? $htmlElementModule.'::components.form' : '';
        } else {
            // otherwise check element is declared like "MyModule::some_element" ...
            $ei = $this->getElementModuleInfo($element);
            // if so ...
            if ($ei['module']) {
                $element = $ei['element'];
                $viewPath = $ei['module'].'::components.form';
            } else {
                // default view path ...
                $viewPath = 'form::components.form';
            }
        }

        // Render the given element
        return view($viewPath.'.'.$element, $viewData)->render();
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

        return $userService->hasUserResource(Auth::user(), AclResource::RES_MANAGE_USERS);
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
     * @return mixed
     */
    public function getOwnerUserId(): mixed
    {
        // 1) Autodetect: Check whether there is an existing user_id ...
        if ($this->getDataSource() && data_get($this->getDataSource(), 'user_id')) {
            return data_get($this->getDataSource(), 'user_id');
        }

        // 2) Check parent id (should be user object)
        if ($id = data_get($this->formLivewire->parentData, 'id')) {
            // @todo: should be check whether parent is User?
            return $id;
        }

        // 3) Try to get user_id by assigned default values
        return data_get($this->formLivewire->objectInstanceDefaultValues, 'user_id', 0);
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

}