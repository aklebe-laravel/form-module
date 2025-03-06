@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    <div class="form-control form-control-label {{ $data['css_classes'] }}">{!! $data['value'] !!}</div>
    @include('form::components.form.hidden')
    @include('form::components.form.element-parts.description')
</div>