@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

@endphp

<div class="form-check form-switch form-label-group {{ $data['css_group'] }}">
    <input {!! $form_instance->calcInputAttributesString($data) !!} />
    @include('form::components.form.element-parts.label')
    @include('form::components.form.element-parts.description')
</div>