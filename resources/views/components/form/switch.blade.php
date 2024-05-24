@php
    /**
     * switch as alternative for checkbox
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
    $id = $name;
@endphp
<div class="form-check form-switch {{ $css_group }}">
    <div class="">
        <input class="form-check-input {{ $css_classes }}"
               type="checkbox"
               role="switch"
               id="{{ $id }}"
               name="{{ $name }}"
               class="form-select "
               @if($xModelName) x-model="{{ $xModelName }}" @endif
               @if($livewire) wire:model="{{ $livewire . '.' . $name }}" @endif
               @if($disabled) disabled="disabled" @endif
               @if($read_only) readonly @endif
               @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
               @if(!$xModelName && $value) checked="checked" @endif
               @if(!$xModelName) value="{{ $value }}" @endif
        >
        @unless(empty($label))
            <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
        @endunless
    </div>

    @unless(empty($description))
        <div class="form-text decent">{{ $description }}</div>
    @endunless
</div>