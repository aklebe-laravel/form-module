@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $data['html_element'] = $data['type'] = 'hidden';
    $attrs = $form_instance->calcInputAttributes($data);
@endphp
{{--<x-input :attributes="$attrs" />--}}
<input {{ $attrs }} />
