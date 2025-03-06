@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $debug ??= false;

    $data['value'] = $data['value'] ?: []; // force array
    $jsAlpineSortedName = str_replace('.','_', 'sortableMultiSelect_'.$data['livewire'].'_'.$data['name']);
    $systemService = app('system_base');
    $keyedValues = $systemService->assignArrayKeysByValue($data['value']);
    $options = array_merge($keyedValues, $data['options']);


    // sort specific
    $xSortTag = '';
    if ($options) {
        $xSortTag = 'x-sort="sortSelectOptionTo($item, $position);debug && dumpToElement([selectOptionValues, selectOptionItems]);"';
    }

    // the important entangle to react with form element
    $entangleWireModel = $data['livewire'].'.'.$data['name'];

    // prepare option items for alpine js
    $jsOptions = [];
    foreach ($options as $_k => $_v) {
        $jsOptions[] = [
            'id' => $_k,
            'label' => $_v,
            'selected' => in_array($_k, $data['value']),
        ];
    }
    $jsSortableConfig = [
        'selectOptionItems' => $jsOptions,
        'selectOptionValues' => [],
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


