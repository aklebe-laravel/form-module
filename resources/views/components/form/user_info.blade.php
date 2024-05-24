@php
    /**
     *
     * @var string $name
     * @var string $label
     * @var \App\Models\User $value
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
    {{ $label }}
    @if ($value)
    <a class="link-secondary {{ $css_classes }}"
       @if($xModelName) x-model="{{ $xModelName }}" @endif
       @if($disabled) disabled="disabled" @endif
       @if($read_only) read_only @endif
       @foreach($html_data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach
       @foreach($x_data as $k => $v) x-{{ $k }}="{{ $v }}" @endforeach
       href="{{ route('user-profile', $value->shared_id) }}"
       target="_blank"
    >
        @if ($value->imageMaker->final_thumb_medium_url ?? null)
            <div class="image-box">
               <img src="{{ $value->imageMaker->final_thumb_medium_url }}"/><br />
            </div>
        @else
            <div class="image-box bg-light text-danger p-4">
                {{ __('No Image') }}
            </div>
        @endif
        {{ $value->name }}
    </a>
    @else
        <span>No User</span>
    @endif
    @unless(empty($description))
        <div class="form-text decent">{{ $description }}</div>
    @endunless

</div>