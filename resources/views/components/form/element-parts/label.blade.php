@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    if ($form_instance->useElementPlaceholders) return;
@endphp
@unless(empty($data['label']))
    @php
        $labelShort = $data['label'];
        if (!empty($data['label_limit'])) {
            $labelShort = Str::limit($data['label'], $data['label_limit']);
        }
    @endphp
    <label class="" @if($data['label'] !== $labelShort) title="{{ $data['label'] }}" @endif>{{ $labelShort }}</label>
@endunless