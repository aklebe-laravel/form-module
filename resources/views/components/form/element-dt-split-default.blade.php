@php
    use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

    /**
     * @var ModelBase $form_instance
     * @var array $data
     */

    $object = $form_instance->dataSource;
    $parentData = [
       'id' => $object['id'] ?? null,
       'model_class' => $form_instance->getObjectEloquentModelName(),
    ];

    $livewireTable = $data['options']['table'] ?? '';
    $livewireTableOptions = $data['options']['table_options'] ?? [];
    $livewireTableOptions['parentData'] = $parentData;
    $livewireTableKey = $form_instance->getStableKey($livewireTable . '-' . $data['name']);

    // Lazy: the table is loaded when it becomes visible (e.g. its tab page is opened).
    // Disable per element with 'options' => ['lazy' => false].
    $lazyParams = ($data['options']['lazy'] ?? true) ? ['lazy' => true] : [];
@endphp
<div class="form-group-responsive-full">
    {{--  @todo: Browser console error if no key used: Uncaught (in promise) TypeError: initialData is null  --}}
    @livewire($livewireTable, array_merge([
        'parentRelationMethodForThisBuilder' => $data['name'],
        'selectedItems' => $data['value'] ? $data['value']->pluck('id')->toArray() : [],
        'selectable' => !$data['disabled'],
    ], $livewireTableOptions, $lazyParams), key($livewireTableKey))
</div>