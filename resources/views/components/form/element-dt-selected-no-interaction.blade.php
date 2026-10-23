@php
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
    use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

    /**
     * @var ModelBase $form_instance
     * @var array $data
     */

    $object = $form_instance->dataSource;
    $options = $data['options'];
    $parentData = [
       'id' => $object['id'] ?? null,
       'model_class' => $form_instance->getObjectEloquentModelName(),
    ];

    $livewireTable = $options['table'] ?? '';
    $livewireTableOptions = $options['table_options'] ?? [];
    $livewireTableOptions['parentData'] = $parentData;
    $livewireTableKey = $form_instance->getStableKey($livewireTable . '-' . $data['name']);

    // Lazy: the table is loaded when it becomes visible (e.g. its tab page is opened).
    // Disable per element with 'options' => ['lazy' => false].
    $lazyParams = ($options['lazy'] ?? true) ? ['lazy' => true] : [];
@endphp
<div class="form-group-responsive-full">
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
    ], $livewireTableOptions, $lazyParams), key($livewireTableKey))
</div>