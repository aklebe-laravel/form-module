@php
    /**
     *
     * @var string $name
     * @var string $label
     * @var mixed $value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $x_model
     * @var string $xModelName
     * @var array $html_data
     * @var array $x_data
     * @var mixed $validator
     * @var string $css_group
     */

    $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
    $_shouldValidateBool = (is_array($validator) && (in_array('bool', $validator)) ? true : (($validator === 'bool') ? true : false));
    $_formattedValue = $value;
    if (is_bool($value) || $_shouldValidateBool) {
        $_formattedValue = $value ? __('Yes') : __('No');
        $css_group .= $value ? ' alert alert-danger' : ' alert alert-success';
    } elseif (is_array($value) || is_object($value)) {
        $_formattedValue = print_r($value, true);
    }
    // $_formattedValue = print_r($validator, true);
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    @include('form::components.form.element-parts.label')
    <div class="form-control-info {{ $css_classes }}"
         class="form-control {{ $css_classes }}"
         @if($xModelName) x-model="{{ $xModelName }}" @endif
         @if($disabled) disabled="disabled" @endif
         @if($read_only) read_only @endif
         @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
         @foreach($x_data as $k => $v) x-{{ $k }}="{{ $v }}" @endforeach

    >{!! $_formattedValue !!}</div>
    @include('form::components.form.element-parts.description')
</div>