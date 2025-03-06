@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $xModelName = (($data['x_model']) ? ($data['x_model'] . '.' . $data['name']) : '');
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    <textarea {!! $form_instance->calcInputAttributesString($data) !!} rows="{{ data_get($data['options'], 'rows', 4) }}">@if(!$xModelName){{ $data['value'] }}@endif</textarea>
    @include('form::components.form.element-parts.description')
</div>