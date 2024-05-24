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
        <label class="">{{ $label }}</label>
    @endunless
    <div class="container image-box">
        @if($value)
            <img
                @if($xModelName) :src="{{ $xModelName }}" @else src="{{ $value }}" @endif
                alt="{{ $value }}"
            >
        @else
            <div class="bg-light text-danger p-4">
                {{ __('No Image') }}
            </div>
        @endif
    </div>
</div>