@php
    /**
    * @var string $name name attribute
    * @var string $label
    * @var Illuminate\Database\Eloquent\Collection $value
    * @var bool $read_only
    * @var string $description
    * @var string $css_classes
    * @var string $x_model optional for alpine.js
    * @var string $xModelName
    * @var array $html_data data attributes
    * @var array $x_data
    * @var string $modelName
    * @var \Modules\Form\app\Forms\Base\ModelBase $form_instance
    */

    $parentData = [
       'id' => $object->id ?? null,
       'model_class' => get_class($object->resource),
    ];

    $livewireTable = $options['table'] ?? '';
    $livewireTableOptions = $options['table_options'] ?? [];
    $livewireTableOptions['parentData'] = $parentData;
    $livewireTableKey = \Modules\SystemBase\app\Services\LivewireService::getKey($livewireTable . '-' . $name);
    // dump($livewireTableKey);
@endphp
<div>
    {{--  @todo: Browser console error if no key used: Uncaught (in promise) TypeError: initialData is null  --}}
    @livewire($livewireTable, array_merge([
        'parentRelationMethodForThisBuilder' => $name,
        'selectedItems' => $value->pluck('id')->toArray(),
        'selectable' => !$disabled,
    ], $livewireTableOptions), key($livewireTableKey))
</div>