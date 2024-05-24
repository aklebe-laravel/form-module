@php
    /**
     *
     * @var string $name
     * @var mixed $value
     * @var string $x_model
     */

    if ($value instanceof \Illuminate\Support\Collection) {
        $value = $value->pluck('id')->toArray(); // @todo: id dynamisch rausfinden
    }
    if (!$value) {
        $value = [];
    }
    if (!is_array($value)) {
        $value = [$value];
    }
    $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
    //dd($value);
@endphp
{{-- Select unterstützt kein read_only wird aber hier die options deaktivieren --}}
<div class="form-group form-label-group {{ $css_group }}">
    @unless(empty($label))
        <label class="">{{ $label }}</label>
    @endunless
    <select
            name="{{ $name }}"
            class="form-select {{ $css_classes }}"
            @if($xModelName) x-model="{{ $xModelName }}" @endif
            @if($disabled) disabled="disabled" @endif
            @if($read_only) readonly @endif
            multiple="multiple"
            size="{{ !empty($list_size) ? $list_size : 6 }}">
        @unless(empty($options))
            @php
                if (app('system_base')->isCallableClosure($options)) {
                    $options = $options();
                }
            @endphp
            @foreach($options as $k => $v)
                <option
                        @if(!$xModelName && in_array($k, $value)) selected="selected" @endif
                value="{{ $k }}"
                        @if($disabled || $read_only) disabled="disabled" @endif
                >{{ $v }}</option>
            @endforeach
        @endunless
    </select>
    @unless(empty($description))
        <div class="form-text decent">{{ $description }}</div>
    @endunless
</div>