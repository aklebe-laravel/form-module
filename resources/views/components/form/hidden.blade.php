@php
    /**
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
<input type="hidden"
       name="{{ $name }}"
       @if($livewire) wire:model="{{ $livewire . '.' . $name }}" @endif
       @if($xModelName) x-model="{{ $xModelName }}" @endif
       value="{{ $value }}"
/>
