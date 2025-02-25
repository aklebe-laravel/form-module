@php
    use Illuminate\Database\Eloquent\Collection;
    use Modules\SystemBase\app\Services\LivewireService;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
    * @var string $name name attribute
    * @var string $label
    * @var Collection $value
    * @var bool $read_only
    * @var string $description
    * @var string $css_classes
    * @var string $x_model optional for alpine.js
    * @var string $xModelName
    * @var array $html_data data attributes
    * @var array $x_data
    * @var string $modelName
    * @var NativeObjectBaseLivewire $form_livewire
    */

    $parentData = [
       'id' => $object->id ?? null,
       'model_class' => is_object($object->resource) ? get_class($object->resource) : null,
    ];

    $livewireTable = $options['table'] ?? '';
    $livewireTableOptions = $options['table_options'] ?? [];
    $livewireTableOptions['parentData'] = $parentData;
    $livewireTableKey = LivewireService::getKey($livewireTable . '-' . $name);
@endphp
<div>
    {{--  @todo: Browser console error if no key used: Uncaught (in promise) TypeError: initialData is null  --}}
    @livewire($livewireTable, array_merge([
        'parentRelationMethodForThisBuilder' => $name,
        'selectedItems' => $value ? $value->pluck('id')->toArray() : [],
        'selectable' => !$disabled,
    ], $livewireTableOptions), key($livewireTableKey))
</div>