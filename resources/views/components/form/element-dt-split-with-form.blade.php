@php
    use Illuminate\Http\Resources\Json\JsonResource;
    use Modules\SystemBase\app\Services\LivewireService;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

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

    // Datatable
    $livewireTable = $options['table'] ?? '';
    $livewireTableOptions = $options['table_options'] ?? [];
    $livewireTableOptions['parentData'] = $parentData;
    $livewireTableKey = LivewireService::getKey($livewireTable . '-' . $data['name']);

    // Form
    $livewireForm = $options['form'] ?? '';
    if (!$livewireForm) {
        throw new Exception('No form declared for equivalent table '. $livewireTable);
    }
    $livewireFormOptions = $options['form_options'] ?? [];
    $livewireFormOptions['parentData'] = $parentData;
    $livewireFormKey = LivewireService::getKey($livewireForm . '-' . $data['name']);
@endphp
<div>
    {{--Form--}}
    <div>
        {{--                            <div class="text-info">(before livewire form)</div>--}}
        @livewire($livewireForm, array_merge([
            'relatedLivewireDataTable' => $livewireTable,
            // The rare place where we assign parent id for sub datatables ...
        ], $livewireFormOptions), key($livewireFormKey))
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
        ], $livewireTableOptions), key($livewireTableKey))
    </div>
</div>