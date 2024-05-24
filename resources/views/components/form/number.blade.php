@php
    /**
     *
     * @var string $name
     * @var string $label
     * @var mixed $value
     * @var bool $read_only
     * @var string $description
     * @var string $stepFormat
     * @var string $css_classes
     * @var string $x_model
     * @var string $xModelName
     * @var string $livewire
     * @var array $html_data
     * @var array $x_data
     */

    $xModelName = (($x_model) ? ($x_model . '.' . $name) : '');
@endphp
<div class="form-group form-label-group {{ $css_group }}">
    @unless(empty($label))
        <label>{{ $label }}</label>
    @endunless
    <input
            type="{{ $type ?? 'number' }}"
            step="{{ $stepFormat ?? '0.01' }}"
            name="{{ $name }}"
            class="form-control {{ $css_classes }}"
            @if($xModelName) x-model="{{ $xModelName }}" @endif
            @if($livewire) wire:model="{{ $livewire . '.' . $name }}" @endif
            @if(!$livewire && !$xModelName) value="{{ $value }}" @endif
            placeholder="{{ $label }}"
            @if($disabled) disabled="disabled" @endif
            @if($read_only) read_only @endif
            @if(!$auto_complete) autocomplete="off" @endif
            @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
            @foreach($x_data as $k => $v) x-{{ $k }}="{{ $v }}" @endforeach
    />
    @unless(empty($description))
        <div class="form-text decent">{{ $description }}</div>
    @endunless
</div>