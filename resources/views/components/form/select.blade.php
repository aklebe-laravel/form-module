@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
    $attrs = $form_instance->calcInputAttributes($data);
    $name = $data['name'];
    $options = $data['options'];
    $options = collect($options)->map(fn($label, $value) => [
        'label' => $label,
        'id' => $value
    ])->values()->toArray();
@endphp
<div class="form-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    @if($data['multiple'] ?? false)
        <x-select.styled
                :attributes="$attrs"
                :options="$options"
                select="label:label|value:id"
                multiple
        />
    @else
        <x-select.native :attributes="$attrs"
                         :options="$options"
                         select="label:label|value:id"
        />
    @endif
    @include('form::components.form.element-parts.description')
</div>