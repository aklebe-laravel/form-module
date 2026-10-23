@php
    use Illuminate\Http\Resources\Json\JsonResource;
    use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

    /**
     * @var ModelBase $form_instance
     * @var array $data
     */

    $object = $form_instance->dataSource;
    $options = $data['options'];
    $parentData = [
       'id' => $object->id ?? null,
       'model_class' => $form_instance->getObjectEloquentModelName(),
    ];

    // Datatable
    $livewireTable = $options['table'] ?? '';
    $livewireTableOptions = $options['table_options'] ?? [];
    $livewireTableOptions['parentData'] = $parentData;
    $livewireTableKey = $form_instance->getStableKey($livewireTable . '-' . $data['name']);

    // Form
    $livewireForm = $options['form'] ?? '';
    if (!$livewireForm) {
        throw new Exception('No form declared for equivalent table '. $livewireTable);
    }
    $livewireFormOptions = $options['form_options'] ?? [];
    $livewireFormOptions['parentData'] = $parentData;
    $livewireFormKey = $form_instance->getStableKey($livewireForm . '-' . $data['name']);

    // Lazy: form and table are loaded when they become visible (e.g. their tab page is opened).
    // Disable per element with 'options' => ['lazy' => false].
    $lazyParams = ($options['lazy'] ?? true) ? ['lazy' => true] : [];
@endphp
<div class="form-group-responsive-full">
    {{--Form--}}
    <div>
        {{--                            <div class="text-info">(before livewire form)</div>--}}
        @livewire($livewireForm, array_merge([
            'relatedLivewireDataTable' => $livewireTable,
            // The rare place where we assign parent id for sub datatables ...
        ], $livewireFormOptions, $lazyParams), key($livewireFormKey))
    </div>

    {{--Datatable--}}
    <div>
        {{--  @todo: Browser console error if no key used: Uncaught (in promise) TypeError: initialData is null  --}}
        @livewire($livewireTable, array_merge([
            'relatedLivewireForm' => $livewireForm,
            'parentRelationMethodForThisBuilder' => $data['name'],
            'selectedItems' => $data['value']->pluck('id')->toArray(),
            'hasCommands' => false, //!$disabled,
            'editable' => false, //!$disabled,
            'selectable' => !$data['disabled'],
        ], $livewireTableOptions, $lazyParams), key($livewireTableKey))
    </div>
</div>