@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $data['html_element'] = $data['type'] = 'email';
@endphp
@include('form::components.form.text')