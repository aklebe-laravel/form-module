@unless(empty($data['label']))
    @php
        $labelShort = $data['label'];
        if (!empty($data['label_limit'])) {
            $labelShort = Str::limit($data['label'], $data['label_limit']);
        }
    @endphp
    <label class="" @if($data['label'] !== $labelShort) title="{{ $data['label'] }}" @endif>{{ $labelShort }}</label>
@endunless