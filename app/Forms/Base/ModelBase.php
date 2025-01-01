<?php

namespace Modules\Form\app\Forms\Base;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\SystemBase\app\Models\JsonViewResponse;

/**
 * Form base class for Eloquent-Models as objects.
 */
class ModelBase extends NativeObjectBase
{
    /**
     * Model name incl. namespace!
     * Can explicit overwritten in class, if no automatic generation by related eloquent model is wanted.
     *
     * @var string|null
     */
    protected ?string $objectEloquentModelName = null;

    /**
     * Model name without namespace.
     * Automatically generated (by \DataTables\ class name) if null.
     *
     * @var string|null
     */
    protected ?string $objectModelName = null;

    /**
     * Default needed relations (used by ->with(...)).
     * Also used by:
     * - blacklist of properties used by array_diff_key() to clean poor model data
     * - onAfterUpdateItem() to sync relations
     *
     * @todo: später Unterscheidung von collection und einzelobjekten sinnvoll, falls die Listen zu lang dauern ...
     *
     * @var array
     */
    protected array $objectRelations = [];

    /**
     * Just overwritten to add the type hint Model
     *
     * @return JsonResource|Model|null
     */
    public function getDataSource(): ?JsonResource
    {
        return parent::getDataSource();
    }

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
            if ($this->frontendKey) {
                $builder->loadByFrontEnd($id, $this->frontendKey);
            } else {
                $builder->whereId($id);
            }

            if ($x = $builder->first()) {
                // parent id (the id from parent form if form in form)
                // @todo: find a better place?
                $x->relatedPivotModelId = data_get($this->formLivewire->parentData, 'id'); // parent id or null
            }
            $this->setDataSource(new JsonResource($x ?? $this->getObjectEloquentModel()));

        } else {
            $this->setDataSource($this->getDataSource() ?: new JsonResource($this->makeObjectModelInstance()));
        }

        return $this->getDataSource();
    }

    /**
     * Get the proper model name without namespace.
     *
     * @return ?string
     */
    public function getObjectModelName(): ?string
    {
        if ($this->objectModelName === null) {
            $this->objectModelName = app('system_base')->getSimpleClassName(static::class);
        }

        return $this->objectModelName;
    }

    /**
     * Get the proper eloquent model name inclusive namespace.
     *
     * @return string
     */
    public function getObjectEloquentModelName(): string
    {
        if ($this->objectEloquentModelName === null) {
            $this->objectEloquentModelName = app('system_base')->findModuleClass($this->getObjectModelName());
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
            'description' => !$this->getDataSource()->getKey() ? 'module_form_ensure_create_instance' : '',
        ];
    }

    /**
     * Check whether data has attributes in $cast and have to transform it before save (like json)
     *
     * @param  array  $cleanData
     * @param  JsonResource|Model  $protoType
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
        $ttlDefault = config('system-base.cache.default_ttl', 1);
        $ttl = config('system-base.cache.object.signature.ttl', $ttlDefault);
        return Cache::remember('form_prototype_'.$this->getObjectEloquentModelName(), $ttl, function () {
            return $this->initDataSource(); // empty resource to know key
        });
    }

    /**
     * @param  array  $itemData
     * @param  JsonViewResponse  $jsonResponse
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

        // Check whether data hast attributes in $cast and we have to transform it before save (like json)
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
                $jsonResponse->setErrorMessage($exception->getMessage());
            }


        } else {

            $collection = $this->getObjectEloquentModel()->with($this->objectRelations);
            if ($objectInstance = $collection->find($id)) {

                $objectInstance->relatedPivotModelId = data_get($this->formLivewire->parentData, 'id'); // parent id or null

                $this->onBeforeUpdateItem($itemData, $jsonResponse, $objectInstance);

                /**
                 * prevent exception:
                 * SQLSTATE[42S22]: Column not found: 1054 Unknown column 'relatedPivotModelId' in 'field list'
                 * @todo: maybe can placed in a cleaner way
                 */
                unset($objectInstance->relatedPivotModelId);

                // Update main data (with $cleanData)
                try {
                    if ($objectInstance->updateTimestamps()->update($cleanData)) {
                        $updateResult['updated'] = $objectInstance->getKey();
                    }
                } catch (Exception $exception) {
                    $jsonResponse->setErrorMessage($exception->getMessage());
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
        $jsonResponse = new JsonViewResponse($this->objectFrontendLabel.' aktualisiert.');
        $successData = [
            'created' => [],
            'updated' => []
        ];

        /**
         * Commonly there is just ONE single item in list!
         */
        foreach ($items as $item) {

            // object data have to present in 'data'
            $itemData = data_get($item, 'data');
            $this->formLivewire->parentData = data_get($item, 'parentData');

            // Validate now
            $itemData = $this->validate($itemData, $jsonResponse);
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

    // /**
    //  * Override to update by api/form.
    //  *
    //  * @param  Request  $request
    //  *
    //  * @return Application|ResponseFactory|Response
    //  * @throws ValidationException
    //  */
    // public function onApiUpdateList(Request $request): Response|Application|ResponseFactory
    // {
    //     $items = $request->all();
    //     $items = data_get($items, 'items');
    //
    //     $jsonResponse = $this->runUpdateList($items);
    //
    //     return $jsonResponse->go();
    // }
    //

    /**
     * Event after saved the object itself.
     * Sync of relations should happen here.
     *
     * @param  array  $itemData
     * @param  JsonViewResponse  $jsonResponse
     * @param  mixed  $objectInstance
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

            if (method_exists($objectInstance, $propertyKey)) { // relation vorhanden ...

                // sync() = BelongsToMany()
                if (method_exists($objectInstance->$propertyKey(), 'sync')) { // ... und kann sync() verwenden ?
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
     * @param  Model  $model
     * @param  string  $propertyKey
     * @param  array  $dataInItems
     *
     * @return bool
     * @throws Exception
     */
    protected function syncHasManyRelation(Model $model, string $propertyKey, array $dataInItems): bool
    {
        if (!method_exists($model->$propertyKey(), 'saveMany')) {
            return false;
        }

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
     * Runs through $this->>objectRelations and returns the rootRelation once.
     *
     * @param  array  $items  Daten aus Formular
     * @param  callable  $callback  Callback für jede Property
     * @param  bool  $ignoreNotPresentedProperties  Wenn gesetzt, dann werden Properties nicht durchlaufen, die nicht in $items vorkommen.
     *
     * @return bool
     */
    public function runObjectRelationsRootProperties(array $items, callable $callback,
        bool $ignoreNotPresentedProperties = true): bool
    {
        $alreadyFound = [];
        /** @var string $propertyKey */
        foreach ($this->objectRelations as $propertyKey) {
            if ($propertyKeyDeep = explode('.',
                $propertyKey)) { // could be more nested (like 'orders.items.options') ...

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
        $this->runObjectRelationsRootProperties($itemData,
            function ($propertyKey, $dataInItems) use (&$preparedDiffKeyArray) {
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
     * @param  JsonResource|null  $dataSource
     * @param  string             $displayKey
     *
     * @return string
     */
    protected function makeFormTitle(?JsonResource $dataSource, string $displayKey): string
    {
        if ($dataSource->getKey()) {
            $result = sprintf(__("Change %s: %s"), __($this->objectFrontendLabel), $dataSource->$displayKey);
        } else {
            $result = sprintf(__("Create %s"), __($this->objectFrontendLabel));
        }

        return $result;
    }

    /**
     * @param  array  $data
     *
     * @return Model
     */
    public function makeObjectModelInstance(array $data = []): Model
    {
        return $this->getObjectEloquentModel()->make(array_merge($this->formLivewire->objectInstanceDefaultValues, $data));
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
    protected function getModelTableColumns(): array
    {
        $ttl = config('system-base.cache.db.signature.ttl', 0);
        $modelName = $this->getObjectEloquentModelName();
        return Cache::remember('form_model_table_columns_'.$modelName, $ttl, function () use ($modelName) {
            return app('system_base')->getDbColumns($this->getModelTable());
        });
    }

    /**
     * Prepare the data like name, value, ... for the view.
     *
     * @param  string  $element
     * @param  string  $name
     * @param  array  $options
     * @param  array  $parentOptions
     *
     * @return array
     */
    public function prepareFormViewData(string $element, string $name, array $options = [],
        array $parentOptions = []): array
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
            $viewData = app('system_base')->arrayRootCopyWhitelistedNoArrays($viewData, $parentOptions,
                $this->inheritViewData);
        }

        // merge/inherit current data
        // @todo: why is arrayCopyWhitelisted() not enough?
        $viewData = app('system_base')->arrayMergeRecursiveDistinct($viewData, $options);

        //
        $this->calculateCallableValues($viewData);

        /**
         * get name by (first given wins)
         * 1) field from viewData['name']
         * 2) field from viewData['property']
         * 3) take parameter $name
         * 4) add parent name to path if given
         */
        $name = data_get($viewData, 'name') ?: data_get($viewData, 'property') ?: $name;
        $name = ($parentName && $name) ? ($parentName.'.'.$name) : $name;

        // Don't include fields not in table columns ro not in resource itself!
        // (filtered out keys like '', '0', 'sdgfdgdfgdfgdfgd' (from livewire?) or other extern stuff in dataSource)
        $allColumns = $this->getModelTableColumns();
        if ((!in_array($name, $this->forceValidElementFields)) && (!in_array($name,
                $allColumns)) && (!app('system_base')->hasData($this->getDataSource()->resource, $name))) {
            return $viewData;
        }

        //
        $resourcePrevValue = data_get($this->getDataSource()->resource, $name);

        /**
         * get value by (first given wins)
         * 1) direct set by form field viewData['value']
         * 2) from dataSource
         * 3) viewData['default'] if given and value is empty
         * @todo: point 3 is questionable especially if value is false, maybe remove 'default' this way
         * @fixed: overwritten null wich was needed
         */
        $value = data_get($viewData, 'value') ?: $resourcePrevValue;;
        if (!$value && ($default = data_get($viewData, 'default', ''))) {
            $value = $default;
        }

        // hits all rooted (non-dotted) properties ...
        if (!str_contains($name, '.')) {
            // Check whether value is a $cast attribute and we have to transform it before view (like json)
            $value = $this->checkViewDataCastAttributeValue($name, $value);
            // object for livewire ...
            data_set($this->getDataSource()->resource, $name, $value);
        }

        // set calculated values for blade templates
        $viewData['value'] = $value ?? '';
        $viewData['name'] = $name;

        //
        return $viewData;
    }

    /**
     * Overwritten to pre load resource model.
     *
     * @param  array  $data
     * @param  JsonViewResponse  $jsonResponse
     * @param  array  $additionalValidateFormat
     * @return array
     * @throws ValidationException
     */
    public function validate(array $data, JsonViewResponse $jsonResponse, array $additionalValidateFormat = []): array
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

        return parent::validate($data, $jsonResponse, $additionalValidateFormat);
    }

}