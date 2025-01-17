@php
    /**
     * Select unterstützt kein ReadOnly wird aber hier die options deaktivieren
     *
     * @var bool $visible maybe always true because we are here
     * @var bool $disabled enabled or disabled
     * @var bool $read_only disallow edit
     * @var bool $auto_complete auto fill user inputs
     * @var string $name name attribute
     * @var string $label label of this element
     * @var mixed $value value attribute
     * @var mixed $default default value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $css_group
     * @var string $x_model optional for alpine.js
     * @var string $livewire
     * @var bool $livewire_live
     * @var int $livewire_debounce
     * @var array $html_data data attributes
     * @var array $x_data
     * @var int $element_index
     * @var array $options
     * @var bool $debug
     */

    $debug ??= false;

    $value = $value ?: []; // force array
    $jsAlpineSortedName = str_replace('.','_', 'sortableMultiSelect_'.$livewire.'_'.$name);
    $systemService = app('system_base');
    $keyedValues = $systemService->assignArrayKeysByValue($value);
    $options = array_merge($keyedValues, $options);


    // sort specific
    $xSortTag = '';
    if ($options) {
        $xSortTag = 'x-sort="sortSelectOptionTo($item, $position);debug && dumpToElement([selectOptionValues, selectOptionItems]);"';
    }

    // the important entangle to react with form element
    $entangleWireModel = $livewire.'.'.$name;

    // prepare option items for alpine js
    $jsOptions = [];
    foreach ($options as $_k => $_v) {
        $jsOptions[] = [
            'id' => $_k,
            'label' => $_v,
            'selected' => in_array($_k, $value),
        ];
    }
    $jsSortableConfig = [
        'selectOptionItems' => $jsOptions,
        'debug' => $debug,
    ];
@endphp
<div
        x-data='new SortableMultiSelect({!! json_encode($jsSortableConfig) !!}, $wire.entangle("{{$entangleWireModel}}"))'
        class="form-group form-label-group">

    <div class="{{ $debug ?: 'd-none' }}">
        @include('form::components.form.multi_select',[
            'x_data'=>[
                'on:change' => 'debug && dumpToElement([selectOptionValues, selectOptionItems])',
            ],
        ])
    </div>

    <div {!! $xSortTag !!} class="container el-select">
        @foreach($jsOptions as $_v)
            <div
                    x-sort:item="'{{ $_v['id'] }}'"
                    x-on:click="toggleOption('{{ $_v['id'] }}');debug && dumpToElement([selectOptionValues, selectOptionItems]);"
                    class="col-12 btn text-start mb-1 el-option btn btn-sm"
                    :class="((isInObject(selectOptionValues,'{{ $_v['id'] }}')) ? 'selected' : '')"
            >{{ $_v['label'] }}</div>
        @endforeach
    </div>

    @if($debug)
        <div class="w-100" style="white-space: pre; background-color: #1a1d20; color: #0dcaf0; height: 400px; overflow: auto;">
            <pre class="dump-area">xxx</pre>
        </div>
    @endif

</div>


