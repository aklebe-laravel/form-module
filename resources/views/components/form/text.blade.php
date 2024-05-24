@php
    /**
     * default input text element
     *
     * @var bool $visible maybe always true because we are here
     * @var bool $disabled enabled or disabled
     * @var bool $read_only disallow edit
     * @var bool $auto_complete auto fill user inputs
     * @var string $name name attribute
     * @var string $id id attribute
     * @var string $label label of this element
     * @var mixed $value value attribute
     * @var mixed $default default value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $css_group
     * @var string $x_model optional for alpine.js
     * @var string $livewire
     * @var string|null $icon
     * @var array $html_data data attributes
     * @var array $x_data
     * @var int $element_index
     * @var Illuminate\Http\Resources\Json\JsonResource $object
     * @var \Modules\Form\app\Forms\Base\ModelBase $form_instance
     */

    $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
    $icon ??= null;
    $type = $type ?? null;
    $_isPassword = ($type === 'password');
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    @unless(empty($label))
        <label>{{ $label }}</label>
    @endunless

    @if ($icon)
        <div class="input-group">
            @endif

            <input
                    type="{{ $type ?? 'text' }}"
                    name="{{ $name }}"
                    @if($id) id="{{ $id }}" @endif
                    class="form-control {{ $css_classes }}"
                    @if($livewire) wire:model="{{ $livewire . '.' . $name }}" @endif
                    value="{{ !$_isPassword ? $value : '' }}"
                    @if ($wireIgnore ?? false) wire:ignore.self @endif
                    placeholder="{{ $label }}"
                    @if($disabled) disabled="disabled" @endif
                    @if($read_only) readonly @endif
                    @if(!$auto_complete) autocomplete="{{ $_isPassword ? 'new-password' : 'off' }}" @endif
                    @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
                    @foreach($x_data as $k => $v) x-{{ $k }}="{{ $v }}" @endforeach
            />
            @if ($icon)
                <span class="input-group-append">
            <button class="btn btn-outline-secondary disabled">
                <span class="{{ $icon }}"></span>
            </button>
        </span>
            @endif

            @if ($icon)
        </div>
    @endif

    <div class="form-text decent">
        @if(!empty($description))
            {{ $description }}
        @else
            &nbsp;
        @endif
    </div>
</div>