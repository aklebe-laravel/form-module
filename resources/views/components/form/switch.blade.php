@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
@endphp
<div class="form-check form-switch {{ $data['css_group'] }}">
    <div class="">
        <input {!! $form_instance->calcInputAttributesString($data) !!} role="switch" />
        @include('form::components.form.element-parts.label')
    </div>
    @include('form::components.form.element-parts.description')
</div>