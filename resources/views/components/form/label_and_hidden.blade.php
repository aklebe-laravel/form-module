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
     */

    $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    @unless(empty($label))
        <label>{{ $label }}</label>
    @endunless
    <div class="form-control form-control-label {{ $css_classes }}"
         @if($xModelName) x-model="{{ $xModelName }}" @endif
         @if($disabled) disabled="disabled" @endif
         @if($read_only) read_only @endif
         @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
         @foreach($x_data as $k => $v) x-{{ $k }}="{{ $v }}" @endforeach

    >{!! $value !!}</div>
    @include('form::components.form.hidden')
    @unless(empty($description))
        <div class="form-text decent">{{ $description }}</div>
    @endunless

</div>