@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $data['multiple'] ??= false;
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    @include('form::livewire.forms.controls.sortable_multi_select')
    @include('form::components.form.element-parts.description')
</div>