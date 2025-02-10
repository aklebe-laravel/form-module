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
     * @var string $xModelName optional for alpine.js
     * @var string $livewire
     * @var string $livewire_click
     * @var string $bs_icon
     * @var bool $livewire_live
     * @var int $livewire_debounce
     * @var array $html_data data attributes
     * @var array $html_data x-on attributes
     * @var array $x_data
     * @var int $element_index
     * @var array $options
     */
    $_liveWireAttr = '';
    if ($livewire) {
        $_liveWireAttr = 'wire:click="'.$livewire.'.'.$name.'"';
    }
@endphp
<button class="form-control {{ $css_classes }}"
        name="{{ $name }}"
        type="button"
        @if($disabled) disabled="disabled" @endif
        @if($read_only) read_only @endif
        @if($_liveWireAttr) {!! $_liveWireAttr !!} @endif
        @if($livewire_click) wire:click="{!! $livewire_click !!}" @endif
>@if(!empty($bs_icon)) <span class="bi bi-{{ $bs_icon }}"></span> @endif {{ $label }}</button>
