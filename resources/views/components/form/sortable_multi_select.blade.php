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
     * @var string $livewire
     * @var bool $livewire_live
     * @var int $livewire_debounce
     * @var array $html_data data attributes
     * @var array $x_data
     * @var int $element_index
     * @var array $options
     */

    $multiple ??= false;
    $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
    $_liveWireAttr = '';
    if ($livewire) {
        $_liveWireAttr = 'wire:model'.(($livewire_live) ? ('.live'.(($livewire_debounce) ? ('.debounce.'.$livewire_debounce.'ms') : '')) : '').'="'.$livewire.'.'.$name.'"';
    }
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    @include('form::components.form.element-parts.label')
    @include('form::livewire.forms.controls.sortable_multi_select')
    @include('form::components.form.element-parts.description')
</div>