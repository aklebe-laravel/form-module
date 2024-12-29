@php
    /**
     * Placeholder entfällt hier, da floating label im textarea immer angezeigt wird
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

    $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    @include('form::components.form.element-parts.label')
    <textarea
            class="form-control {{ $css_classes }}"
            name="{{ $name }}"
            rows="{{ data_get($options, 'rows', 4) }}"
            @if($xModelName) x-model="{{ $xModelName }}" @endif
            @if($livewire) wire:model="{{ $livewire . '.' . $name }}" @endif
            @if($disabled) disabled="disabled" @endif
            @if($read_only) readonly @endif
            @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
            @foreach($x_data as $k => $v) x-{{ $k }}="{{ $v }}" @endforeach
    >@if(!$xModelName){{ $value }}@endif</textarea>
    @include('form::components.form.element-parts.description')
</div>