@php
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
    use Modules\SystemBase\app\Services\LivewireService;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    /* @var JsonResource $object */
    $object = $form_instance->getDataSource();
    $options = $data['options'];
    $parentData = [
       'id' => $object->id ?? null,
       'model_class' => is_object($object->resource) ? get_class($object->resource) : null,
    ];

    $livewireTable = $options['table'] ?? '';
    $livewireTableOptions = $options['table_options'] ?? [];
    $livewireTableOptions['parentData'] = $parentData;
    $livewireTableKey = LivewireService::getKey($livewireTable . '-' . $data['name'].'-'.data_get($parentData, 'id', 'x'));
@endphp
<div>
    {{--  @todo: Browser console error if no key used: Uncaught (in promise) TypeError: initialData is null  --}}
    @livewire($livewireTable, array_merge([
        'parentRelationMethodForThisBuilder' => $data['name'],
        'selectedItems' => $data['value']->pluck('id')->toArray(),
        'hasCommands' => false,
        'editable' => false,
        'selectable' => false,
        'enabledCollectionNames' => [ // enable only the selected table
            BaseDataTable::COLLECTION_NAME_SELECTED_ITEMS => true,
        ],
    ], $livewireTableOptions), key($livewireTableKey))
</div>