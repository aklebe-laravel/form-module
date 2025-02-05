@unless(empty($label))
    @php
        $labelShort = $label;
        if (!empty($label_limit)) {
            $labelShort = Str::limit($label, $label_limit);
        }
    @endphp
    <label class="" @if($label !== $labelShort) title="{{ $label }}" @endif>{{ $labelShort }}</label>
@endunless

