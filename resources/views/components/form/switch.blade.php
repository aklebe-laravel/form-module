@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
    $attrs = $form_instance->calcInputAttributes($data);
@endphp
<div class="form-group {{ $data['css_group'] }}">
    <label class="w-80 inline-flex cursor-pointer p-3 bg-surface-200/50 border border-surface-300/50 text-primary-500 rounded-lg focus:ring-accent-800 focus:border-accent-800 block w-full transition duration-200">
        <x-toggle :attributes="$attrs" />
        <div class="ms-2.5 select-none pl-2">
            <p class="text-sm font-medium text-heading mb-1">@include('form::components.form.element-parts.label')</p>
            <p class="text-sm font-normal text-body">@include('form::components.form.element-parts.description')</p>
    </div>
</label>
</div>