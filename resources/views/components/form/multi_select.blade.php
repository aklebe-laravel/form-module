@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $data['value'] = $data['value'] ?: []; // force array
    $data['multiple'] = true;
@endphp
@include('form::components.form.select')
