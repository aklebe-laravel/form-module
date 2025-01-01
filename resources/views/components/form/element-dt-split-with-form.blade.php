@php
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Modules\Form\app\Forms\Base\NativeObjectBase;
    use Modules\SystemBase\app\Services\LivewireService;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * @var bool $visible maybe always true because we are here
     * @var bool $disabled enabled or disabled
     * @var bool $read_only disallow edit
     * @var bool $auto_complete auto fill user inputs
     * @var string $name name attribute
     * @var string $label label of this element
     * @var Collection $value value attribute
     * @var mixed $default default value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $css_group
     * @var string $x_model optional for alpine.js
     * @var string $livewire
     * @var array $html_data data attributes
     * @var array $x_data
     * @var int $element_index
     * @var JsonResource $object
     * @var NativeObjectBase $form_instance
     * @var NativeObjectBaseLivewire $form_livewire
    */

    $parentData = [
       'id' => $object->id ?? null,
       'model_class' => is_object($object->resource) ? get_class($object->resource) : null,
    ];

    // Datatable
    $livewireTable = $options['table'] ?? '';
    $livewireTableOptions = $options['table_options'] ?? [];
    $livewireTableOptions['parentData'] = $parentData;
    $livewireTableKey = LivewireService::getKey($livewireTable . '-' . $name);

    // Form
    $livewireForm = $options['form'] ?? '';
    if (!$livewireForm) {
        throw new Exception('No form declared for equivalent table '. $livewireTable);
    }
    $livewireFormOptions = $options['form_options'] ?? [];
    $livewireFormOptions['parentData'] = $parentData;
    $livewireFormKey = LivewireService::getKey($livewireForm . '-' . $name);
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
            'parentRelationMethodForThisBuilder' => $name,
            'selectedItems' => $value->pluck('id')->toArray(),
            'hasCommands' => false, //!$disabled,
            'editable' => false, //!$disabled,
            'selectable' => !$disabled,
        ], $livewireTableOptions), key($livewireTableKey))
    </div>
</div>