@php
    /**
     * default checkbox element
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

<div class="form-check form-switch form-label-group {{ $css_group }}">
    <input
            type="{{ $type ?? 'checkbox' }}"
            name="{{ $name }}"
            class="form-check-input form-switch {{ $css_classes }}"
            @if($xModelName) x-model="{{ $xModelName }}" @endif
            @if($livewire) wire:model="{{ $livewire . '.' . $name }}" @endif
            @if($disabled) disabled="disabled" @endif
            @if($read_only) readonly @endif
            @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
            @if(!$xModelName && $value) checked="checked" @endif
            @if(!$xModelName) value="{{ $value }}" @endif
    />
    @unless(empty($label))
        <label class="form-check-label">{{ $label }}</label>
    @endunless
    @unless(empty($description))
        <div class="form-text decent">{{ $description }}</div>
    @endunless
</div>