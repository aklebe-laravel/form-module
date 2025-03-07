<?php

namespace Modules\Form\app\Http\Livewire\Form\Base;

use Closure;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Modules\SystemBase\app\Models\JsonViewResponse;
use Modules\SystemBase\app\Services\CacheService;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;

/**
 * the Livewire Form Part.
 */
class ModelBase extends NativeObjectBase
{
    use WithFileUploads;

    /**
     * Default needed relations (used by ->with(...)).
     * Also used by:
     * - blacklist of properties used by array_diff_key() to clean poor model data
     * - onAfterUpdateItem() to sync relations
     *
     * @var array
     */
    public array $objectRelations = [];

    /**
     * Current data object for all relevant data in background.
     *
     * @var JsonResource|Model|null
     */
    protected JsonResource|null $dataSource = null;

    /**
     * Model name incl. namespace!
     * Can explicit overwritten in class, if no automatic generation by related eloquent model is wanted.
     *
     * @var string|null
     */
    protected ?string $objectEloquentModelName = null;

    /**
     * Set for example 'web_uri' or 'shared_id' to try load from this property if is not numeric in initDataSource().
     * Model have to be trait by TraitBaseModel to become loadByFrontEnd()
     *
     * @var string
     */
    public const string frontendKey = '';

    /**
     * Can be overwritten, but shouldn't be needed, because it will return the proper model instance.
     * If id is invalid, an empty object will be returned.
     *
     * @param  mixed|null  $id
     *
     * @return JsonResource
     */
    public function initDataSource(mixed $id = null): JsonResource
    {
        if ($id) {

            $builder = $this->getObjectEloquentModel()->with($this->objectRelations);
            if (static::frontendKey) {
                $builder->loadByFrontEnd($id, static::frontendKey);
            } else {
                $builder->whereId($id);
            }

            if ($x = $builder->first()) {
                // parent id (the id from parent form if form in form)
                // @todo: find a better place?
                $x->relatedPivotModelId = data_get($this->parentData, 'id'); // parent id or null
            } else {
                // Not found error or 404 ...
                $this->addErrorMessage(__(':name not found.', ['name' => __($this->objectFrontendLabel)]));
                $this->closeForm();
            }
            $this->setDataSource(new JsonResource($x ?? $this->getObjectEloquentModel()));

        } else {
            $this->setDataSource($this->getDataSource() ?: new JsonResource($this->makeObjectModelInstance()));
        }

        return $this->getDataSource();
    }

    /**
     * Get the proper eloquent model name inclusive namespace.
     *
     * @return string
     */
    public function getObjectEloquentModelName(): string
    {
        if ($this->objectEloquentModelName === null) {
            $this->objectEloquentModelName = app('system_base')->findModuleClass(app('system_base')->getSimpleClassName(static::class));
        }

        return $this->objectEloquentModelName;
    }

    /**
     * Get the proper eloquent model instance.
     *
     * @return Model
     */
    public function getObjectEloquentModel(): Model
    {
        return app($this->getObjectEloquentModelName());
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
        if ($item = app('system_base')->getEloquentModelBuilder(app('system_base')->getSimpleClassName($this->getObjectEloquentModelName()))->whereKey($id)->first()) {

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
        // Model have to exists ...
        if ($modelLoaded = $this->initDataSource($this->formObjectId)) {

            // format to id array
            $this->runObjectRelationsRootProperties($this->dataTransfer, function ($propertyKey, $dataInItems) use ($modelLoaded) {

                // In general, we need sync only.
                // No need for saveMany() because we want exactly what we selected in datatables.
                if ($hasSync = method_exists($modelLoaded->$propertyKey(), 'sync')) { // || ($hasSaveMany = method_exists($modelLoaded->$propertyKey(), 'saveMany'))) {

                    $idArray = [];
                    // each relation like 'mediaItems'
                    foreach ($this->dataTransfer[$propertyKey] as $v) {
                        if (is_array($v)) {
                            if ($hasSync && isset($v['id'])) {
                                // use ids only
                                $idArray[] = $v['id']; // @todo: resolve id
                            } elseif ($hasSaveMany ?? false) {
                                // use whole object
                                $idArray[] = $v;
                            }
                        }
                    }

                    $this->dataTransfer[$propertyKey] = $idArray;

                }

            });

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
     * Called by save() or other high level calls.
     *
     * @return JsonViewResponse
     */
    protected function saveFormData(): JsonViewResponse
    {
        if ($this->validateForm()) {

            try {

                $updateList = [
                    [
                        'data'       => $this->dataTransfer,
                        'parentData' => $this->parentData,
                    ],

                ];

                return $this->runUpdateList($updateList);

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
     * Livewire event when updating relations (sub datatable checkbox)
     *
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

    /**
     * @param  array  $data
     *
     * @return Model
     */
    public function makeObjectModelInstance(array $data = []): Model
    {
        /** @var Model|TraitAttributeAssignment $x */
        return $this->getObjectEloquentModel()->newInstance(app('system_base')->arrayMergeRecursiveDistinct($this->objectInstanceDefaultValues, $data));
    }

    /**
     * @return string
     */
    protected function getModelTable(): string
    {
        $modelName = $this->getObjectEloquentModelName();

        return app('system_base')->getModelTable($modelName);
    }

    /**
     * @return array
     */
    public function getModelTableColumns(): array
    {
        return app(CacheService::class)->rememberUseConfig('form_model_table_columns_'.$this->getObjectEloquentModelName(), 'system-base.cache.db.signature.ttl', function () {
            return app('system_base')->getDbColumns($this->getModelTable());
        });

    }


    /**
     * Prepare the data like name, value, ... for the view.
     *
     *
     *
     * @param  string        $element
     * @param  string        $name
     * @param  array         $options
     * @param  array         $parentOptions
     * @param  Closure|null  $callbackExtraValidate
     * @param  Closure|null  $callbackTransformValue  *
     *
     * @return array
     */
    public function prepareFormViewData(string $element, string $name, array $options = [], array $parentOptions = [], ?Closure $callbackExtraValidate = null, ?Closure $callbackTransformValue = null): array
    {
        return parent::prepareFormViewData($element,
            $name,
            $options,
            $parentOptions,
            function ($name) {
                // Don't include fields not in table columns or not in resource itself!
                // (filtered out keys like '', '0', 'sdgfdgdfgdfgdfgd' (from livewire?) or other extern stuff in dataSource)
                $allColumns = $this->getModelTableColumns();
                if ((!in_array($name, $this->forceValidElementFields)) && (!in_array($name, $allColumns)) && (!app('system_base')->hasData($this->getDataSource(), $name))) {
                    return false;
                }

                return true;
            },
            function ($name, $value) {
                // hits all rooted (non-dotted) properties ...
                if (!str_contains($name, '.')) {
                    // Check whether value is a $cast attribute, and we have to transform it before view (like json)
                    $value = $this->checkViewDataCastAttributeValue($name, $value);
                    // object for livewire ...
                    data_set($this->dataSource, $name, $value);
                }

                return $value;
            });
    }

    /**
     * Overwritten to pre load resource model.
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
        // We should load the original model first to get all elements
        // having conditions like can_edit depends on the loaded resource!
        // So check resource exists and key is present, otherwise load it.
        if ($maybeEmptyResource = $this->initDataSource()) {
            if (!$maybeEmptyResource->resource->getKey()) {
                $keyName = $maybeEmptyResource->resource->getKeyName();
                if ($keyValue = data_get($data, $keyName)) {
                    $this->initDataSource($keyValue);
                }
            }
        }

        return parent::validateFormData($data, $jsonResponse, $additionalValidateFormat);
    }


    /**
     * Check whether data has attributes in $cast and have to transform it before save (like json)
     *
     * @param  array               $cleanData
     * @param  JsonResource|Model  $protoType
     *
     * @return void
     */
    protected function checkUpdateItemCasteAttributes(array &$cleanData, JsonResource|Model $protoType): void
    {
        foreach ($cleanData as $k => $v) {
            if ($protoType->hasCast($k, ['array', 'object'])) {
                $cleanData[$k] = json_decode($v, true);
            }
        }
    }

    /**
     * This is like a singleton.
     * Don't change their data!
     *
     * @return JsonResource|Model
     */
    public function getEloquentModelPrototyp(): JsonResource|Model
    {
        return app(CacheService::class)->rememberUseConfig('form_prototype_'.$this->getObjectEloquentModelName(), 'system-base.cache.object.signature.ttl', function () {
            return $this->initDataSource(); // empty resource to know key
        });

    }

    /**
     * @param  array             $itemData
     * @param  JsonViewResponse  $jsonResponse
     *
     * @return array
     * @throws Exception
     */
    public function updateItem(array $itemData, JsonViewResponse $jsonResponse): array
    {
        $updateResult = parent::updateItem($itemData, $jsonResponse);

        // @todo: cache
        /** @var JsonResource|Model $protoType */
        $protoType = $this->getEloquentModelPrototyp(); // empty resource to know key

        // cleanup data to saving poor main object
        $cleanData = $this->getCleanObjectDataForSaving($itemData);

        // Check whether data hast attributes in $cast, and we have to transform it before save (like json)
        $this->checkUpdateItemCasteAttributes($cleanData, $protoType);

        /**
         * Do not change this behavior!
         * 1) Create if needed
         * 2) Assign Attributes
         * 3) Save or update
         * 4) Model have to exist and id have to present now
         * 5) reset cached attributes
         * 6) do relation stuf calling onAfterUpdateItem()
         *
         * @todo: Build feature test for create and update!
         */
        // If no id, create new item ...
        if (!($id = ($itemData[$protoType->getKeyName()] ?? 0))) {
            // Create the object here to get the id
            // but be able to update below!
            $objectInstance = $this->makeObjectModelInstance($cleanData);

            $this->onBeforeUpdateItem($itemData, $jsonResponse, $objectInstance);

            try {
                if ($objectInstance->save()) {
                    $updateResult['created'] = $objectInstance->getKey();
                }
            } catch (Exception $exception) {
                $jsonResponse->setErrorMessage(__('Failed to save.'));
                Log::error($exception->getMessage());
            }


        } else {

            $collection = $this->getObjectEloquentModel()->with($this->objectRelations);
            if ($objectInstance = $collection->find($id)) {

                $objectInstance->relatedPivotModelId = data_get($this->parentData, 'id'); // parent id or null

                $this->onBeforeUpdateItem($itemData, $jsonResponse, $objectInstance);

                /**
                 * prevent exception:
                 * SQLSTATE[42S22]: Column not found: 1054 Unknown column 'relatedPivotModelId' in 'field list'
                 *
                 * @todo: maybe can placed in a cleaner way
                 */
                unset($objectInstance->relatedPivotModelId);

                // Update main data (with $cleanData)
                try {
                    if ($objectInstance->updateTimestamps()->update($cleanData)) {
                        $updateResult['updated'] = $objectInstance->getKey();
                    }
                } catch (Exception $exception) {
                    $jsonResponse->setErrorMessage(__('Failed to update.'));
                    Log::error($exception->getMessage());
                }
            }
        }

        $updateResult['object_instance'] = $objectInstance;

        return $updateResult;
    }

    /**
     * @param  array  $items
     *
     * @return JsonViewResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function runUpdateList(array $items): JsonViewResponse
    {
        $jsonResponse = new JsonViewResponse(__(":name updated.", ['name' => __($this->objectFrontendLabel)]));
        $successData = [
            'created' => [],
            'updated' => [],
        ];

        /**
         * Commonly there is just ONE single item in list!
         */
        foreach ($items as $item) {

            // object data have to present in 'data'
            $itemData = data_get($item, 'data');
            $this->parentData = data_get($item, 'parentData');

            // Validate now
            $itemData = $this->validateFormData($itemData, $jsonResponse);
            if (!$itemData || $jsonResponse->hasErrors()) {
                return $jsonResponse;
            }

            $updateResult = $this->updateItem($itemData, $jsonResponse);

            if ($updateResult['updated']) {
                $successData['updated'][] = $updateResult['updated'];
            }
            if ($updateResult['created']) {
                $successData['created'][] = $updateResult['created'];
            }

            if ($updateResult['updated'] || $updateResult['created']) {
                // use $itemData instead of $cleanData
                $this->onAfterUpdateItem($itemData, $jsonResponse, $updateResult['object_instance']);
            }

        }

        $jsonResponse->setData($successData);

        return $jsonResponse;
    }

    /**
     * Event after saved the object itself.
     * Sync of relations should happen here.
     *
     * @param  array             $itemData
     * @param  JsonViewResponse  $jsonResponse
     * @param  mixed             $objectInstance
     *
     * @return bool
     * @throws Exception
     */
    public function onAfterUpdateItem(array $itemData, JsonViewResponse $jsonResponse, mixed $objectInstance): bool
    {
        // Update relations
        /** @var Model $objectInstance */
        $this->runObjectRelationsRootProperties($itemData, function ($propertyKey, $dataInItems) use ($objectInstance) {

            // properties from frontend form data exists in snake case,
            // but we need camelCase for relations like defined in Model Form class
            $propertyKey = Str::camel($propertyKey);

            if (method_exists($objectInstance, $propertyKey)) { // relation exists ...

                // sync() = BelongsToMany()
                if (method_exists($objectInstance->$propertyKey(), 'sync')) { // ... and can use sync()?
                    try {

                        if (is_array($dataInItems)) {
                            $sync = [];

                            //
                            foreach ($dataInItems as $objectDataKey => $objectData) {

                                // @todo: 'id' dynamically
                                if ($id = data_get($objectData, 'id')) {
                                    if (($pivot = data_get($objectData, 'pivot')) && (is_array($pivot))) {
                                        foreach ($pivot as $pivotKey => $pivotValue) {
                                            $modelId = $pivotValue;
                                            $sync[$id] = [$pivotKey => $modelId];
                                        }
                                    }
                                } else {
                                    // @todo: Has key any relevance?
                                    $sync[] = $objectData;
                                }
                            }

                            // Event before Sync ...
                            $sync = $this->beforeSync($sync);

                            // Sync it ...
                            $objectInstance->$propertyKey()->sync($sync);
                        }

                    } catch (Exception $ex) {
                        // @todo: belongsTo() missing ...
                        Log::error('Error in sync() '.$propertyKey, ['onAfterUpdateItem']);
                        Log::error($dataInItems);
                        Log::error($ex->getMessage());
                        Log::error($ex->getTraceAsString());
                    }

                } elseif (method_exists($objectInstance->$propertyKey(), 'saveMany')) {
                    $this->syncHasManyRelation($objectInstance, $propertyKey, $dataInItems);
                } else {
                    // @TODO: A property which cannot be synced. Maybe no matter because of relation id in object should do the stuff already.
                }
            } else {
                Log::error('Property '.$propertyKey.' is not a relation method.', ['onAfterUpdateItem']);
            }

        }, true);

        // update pivots
        foreach ($itemData as $k => $v) {
            if ($objectInstance->hasAttributeMutator($k)) {
                if ($newPivotData = data_get($v, 'pivot', [])) {
                    if ($objectInstance->$k && $objectInstance->$k->pivot) {
                        $objectInstance->$k->pivot->update($newPivotData);
                    } // else : no parent id?
                } // else no pivot data
            }
        }

        return true;
    }

    /**
     * @param  Model   $model
     * @param  string  $propertyKey
     * @param  array   $dataInItems
     *
     * @return bool
     * @throws Exception
     */
    protected function syncHasManyRelation(Model $model, string $propertyKey, array $dataInItems): bool
    {
        if (!method_exists($model->$propertyKey(), 'saveMany')) {
            return false;
        }

        // I want to know if it's still possible to be here ...
        Log::info("Check this!", [$model::class, $propertyKey, $dataInItems, __METHOD__]);

        $existingIds = [];
        if ($model->$propertyKey) {
            if ($existingFirstRelation = $model->$propertyKey->first()) {
                $existingIds = $model->$propertyKey->pluck($existingFirstRelation->getKeyName())->toArray();
            }
        }

        $upsertList = [];
        $idList = [];
        // check $dataInItems format
        foreach ($dataInItems as $dataInItem) {
            if (is_scalar($dataInItem)) {
                $idList[] = $dataInItem;
            } else {
                // @todo: 'id' dynamically
                $idList[] = data_get($dataInItem, 'id');
            }
        }

        $deleteList = array_diff($existingIds, $idList);

        // ---------------------------
        // Create/Update relations ...
        // ---------------------------
        foreach ($idList as $item) {
            $upsertList[] = [
                'id'      => $item,
                'user_id' => $model->getKey(),
            ];
        }

        // ---------------------------
        // Delete relations this way ...
        // ---------------------------
        if ($deleteList) {
            foreach ($deleteList as $item) {
                $upsertList[] = [
                    'id'      => $item,
                    'user_id' => null,
                ];
            }
        }

        $model->$propertyKey()->upsert($upsertList, ['id'], ['user_id']);

        return true;
    }

    /**
     * Runs through $this->objectRelations and returns the rootRelation once.
     *
     * @param  array     $items                         Daten aus Formular
     * @param  callable  $callback                      Callback für jede Property
     * @param  bool      $ignoreNotPresentedProperties  Wenn gesetzt, dann werden Properties nicht durchlaufen, die nicht in $items vorkommen.
     *
     * @return bool
     */
    public function runObjectRelationsRootProperties(array $items, callable $callback, bool $ignoreNotPresentedProperties = true): bool
    {
        $alreadyFound = [];
        /** @var string $propertyKey */
        foreach ($this->objectRelations as $propertyKey) {
            if ($propertyKeyDeep = explode('.', $propertyKey)) { // could be more nested (like 'orders.items.options') ...

                $propertyKey = $propertyKeyDeep[0];

                if (isset($alreadyFound[$propertyKey])) {
                    continue;
                }
                $alreadyFound[$propertyKey] = true;

                if ($ignoreNotPresentedProperties) {
                    if (($dataInItems = data_get($items, $propertyKey)) !== null) {
                        $callback($propertyKey, $dataInItems);
                    }
                } else {
                    $dataInItems = data_get($items, $propertyKey, []);
                    $callback($propertyKey, $dataInItems);
                }
            }
        }

        return true;
    }

    /**
     * Get data without relations (from json)
     *
     * @param  array  $itemData
     *
     * @return array
     */
    public function getCleanObjectDataForSaving(array $itemData): array
    {
        $preparedDiffKeyArray = [];

        // remove relations
        $this->runObjectRelationsRootProperties($itemData, function ($propertyKey, $dataInItems) use (&$preparedDiffKeyArray) {
            $preparedDiffKeyArray[$propertyKey] = [];
        });

        // get diff
        $result = array_diff_key($itemData, $preparedDiffKeyArray);

        // remove mutators
        foreach ($result as $k => $v) {
            if ($this->getDataSource()->hasAttributeMutator($k)) {
                unset($result[$k]);
            }
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
     * Overwritten to add the typehint model.
     *
     * @return JsonResource|Model|null
     */
    public function getDataSource(): ?JsonResource
    {
        return parent::getDataSource();
    }

    /**
     * Overwrite this method to define your form.
     * Call this method as parent::getFormElements() before to prepare the defaults.
     *
     * @return array
     */
    public function getFormElements(): array
    {
        return [
            'object'      => $this->getDataSource(),
            'css_classes' => 'form-edit',
            'livewire'    => 'dataTransfer',
            'title'       => $this->makeFormTitle($this->getDataSource(), 'id'),
            'description' => !$this->getDataSource()->getKey() ? __('module_form_ensure_create_instance', ['name' => $this->objectFrontendLabel]) : '',
        ];
    }

    /**
     * @param  JsonResource|null  $dataSource
     * @param  string             $displayKey
     *
     * @return string
     */
    protected function makeFormTitle(?JsonResource $dataSource, string $displayKey): string
    {
        /** @var JsonResource|Model|null $dataSource */
        if (!$this->canEdit()) {
            $result = sprintf(__("Show %s: %s"), __($this->objectFrontendLabel), $dataSource->$displayKey);
        } elseif ($dataSource && $dataSource->getKey()) {
            $result = sprintf(__("Change %s: %s"), __($this->objectFrontendLabel), $dataSource->$displayKey);
        } else {
            $result = sprintf(__("Create %s"), __($this->objectFrontendLabel));
        }

        return $result;
    }

}
