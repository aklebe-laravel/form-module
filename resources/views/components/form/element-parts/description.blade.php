@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
    $_label = null;
    $_description = $data['description'] ?? null;
    if ($form_instance->useElementPlaceholders) {
        $_label = $data['label'] ?? null;
    }
@endphp
@unless(empty($_label))
    <span class="form-text-accent">
        {!! $_description ? ($_label . ':') : $_label !!}
    </span>
@endunless
@unless(empty($_description))
    <span class="form-text">
        {!! $_description !!}
    </span>
@endunless
