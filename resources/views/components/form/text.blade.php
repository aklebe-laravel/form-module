@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $icon = $data['icon'] ?? null;
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    @if ($icon)
        <div class="input-group">
            @endif

            <input {!! $form_instance->calcInputAttributesString($data) !!} />

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
    @include('form::components.form.element-parts.description')
</div>