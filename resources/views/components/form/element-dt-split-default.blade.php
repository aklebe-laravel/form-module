@php
    use Modules\SystemBase\app\Services\LivewireService;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    /* @var JsonResource $object */
    $object = $form_instance->getDataSource();
    $parentData = [
       'id' => $object->id ?? null,
       'model_class' => is_object($object->resource) ? get_class($object->resource) : null,
    ];

    $livewireTable = $data['options']['table'] ?? '';
    $livewireTableOptions = $data['options']['table_options'] ?? [];
    $livewireTableOptions['parentData'] = $parentData;
    $livewireTableKey = LivewireService::getKey($livewireTable . '-' . $data['name']);
@endphp
<div>
    {{--  @todo: Browser console error if no key used: Uncaught (in promise) TypeError: initialData is null  --}}
    @livewire($livewireTable, array_merge([
        'parentRelationMethodForThisBuilder' => $data['name'],
        'selectedItems' => $data['value'] ? $data['value']->pluck('id')->toArray() : [],
        'selectable' => !$data['disabled'],
    ], $livewireTableOptions), key($livewireTableKey))
</div>