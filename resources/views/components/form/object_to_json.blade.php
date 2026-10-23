@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $data['options'] = ['rows' => data_get($data['options'], 'rows', 8)];
@endphp
@include('form::components.form.textarea')