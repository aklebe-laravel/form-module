@php
    /**
     * default button
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
     * @var array $html_data data attributes
     * @var array $x_data
     * @var int $element_index
     */
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    <button class="form-control {{ $css_classes }}" name="{{ $name }}" @if($disabled) disabled="disabled" @endif @if($read_only) read_only @endif>{{ $label }}</button>
</div>