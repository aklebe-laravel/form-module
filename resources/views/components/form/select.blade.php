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
     * @var string $xModelName optional for alpine.js
     * @var string $livewire
     * @var bool $livewire_live
     * @var int $livewire_debounce
     * @var array $html_data data attributes
     * @var array $html_data x-on attributes
     * @var array $x_data
     * @var int $element_index
     * @var array $options
     */

    $multiple ??= false;
    if (!isset($xModelName)) {
        $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
    }
    $_liveWireAttr = '';
    if ($livewire) {
        $_liveWireAttr = 'wire:model'.(($livewire_live) ? ('.live'.(($livewire_debounce) ? ('.debounce.'.$livewire_debounce.'ms') : '')) : '').'="'.$livewire.'.'.$name.'"';
    }
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    @include('form::components.form.element-parts.label')
    <select
            name="{{ $name }}"
            class="form-select {{ $css_classes }}"
            @if($xModelName) x-model="{{ $xModelName }}" @endif
            @if($_liveWireAttr) {!! $_liveWireAttr !!} @endif
            @if ($wireIgnore ?? false) wire:ignore.self @endif
            @if($disabled) disabled="disabled" @endif
            @if($multiple) multiple="multiple" size="{{ !empty($list_size) ? $list_size : 6 }}" @endif
            @if($read_only) readonly @endif
            @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
            @foreach($x_data as $k => $v) x-{{ $k }}="{{ $v }}" @endforeach
    >
        @unless(empty($options))
            @php
                if (app('system_base')->isCallableClosure($options)) {
                    $options = $options();
                }
            @endphp
            @foreach($options as $k => $v)
                @if(isset($cmpCi) && $cmpCi)
                    {{--@todo: extra logic like strCaseCompare() will be ignored here if wire is enabled above--}}
                    <option
                            @if(!$xModelName && app('system_base')->strCaseCompare($k, $value)) selected="selected" @endif
                    value="{{ $k }}"
                            @if(($k != $value) && ($disabled || $read_only)) disabled="disabled" @endif
                    >{{ $v }}</option>
                @else
                    @php
                        $_valueHit = (is_array($value) || is_object($value)) ? (in_array($k, (array)$value)) : ($k == $value);
                    @endphp
                    <option
                            @if((!$xModelName) && ($_valueHit)) selected="selected" @endif
                    value="{{ $k }}"
                            @if((!$_valueHit) && ($disabled || $read_only)) disabled="disabled" @endif
                    >{{ $v }}</option>
                @endif
            @endforeach
        @endunless
    </select>
    @include('form::components.form.element-parts.description')
</div>