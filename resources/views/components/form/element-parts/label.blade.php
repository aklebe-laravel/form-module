@unless(empty($label))
    @php
        if (!empty($label_limit)) {
            $label = Str::limit($label, $label_limit);
        }
    @endphp
    <label class="">{{ $label }}</label>
@endunless

