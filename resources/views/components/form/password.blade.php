@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $data['html_element'] = $data['type'] = 'password';
    $data['auto_complete'] = false;
@endphp
@include('form::components.form.text')