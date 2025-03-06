@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    <input {!! $form_instance->calcInputAttributesString($data) !!} step="{{ $stepFormat ?? '0.01' }}" />
    @include('form::components.form.element-parts.description')
</div>