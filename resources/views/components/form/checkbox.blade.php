@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
    $attrs = $form_instance->calcInputAttributes($data);
@endphp

<div class="form-check form-switch form-label-group {{ $data['css_group'] }}">
    <x-checkbox :attributes="$attrs" />
    @include('form::components.form.element-parts.label')
    @include('form::components.form.element-parts.description')
</div>