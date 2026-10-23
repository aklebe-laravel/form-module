@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
    $data['type'] = 'number';
    $data['stepFormat'] ??= '0.01';
    $attrs = $form_instance->calcInputAttributes($data);
@endphp
<div class="form-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    <x-number :attributes="$attrs" step="{{ $data['stepFormat'] }}" />

{{--    <x-select.styled label="Farbe wählen" :options="['Rot', 'Blau', 'Grün']" multiple />--}}
    @include('form::components.form.element-parts.description')
</div>