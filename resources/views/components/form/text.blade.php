@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $data['type'] ??= 'text';
    $icon = $data['icon'] ?? null;
    $attrs = $form_instance->calcInputAttributes($data);
@endphp
<div class="form-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    @if ($icon)
        <div class="input-group">
            @endif

            <x-input :attributes="$attrs" />

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