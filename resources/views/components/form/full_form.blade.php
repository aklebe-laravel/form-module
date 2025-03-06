@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * @var NativeObjectBaseLivewire $form_instance
     * @var array $data
     **/

    $parentInheritVars = get_defined_vars();
@endphp
{!! $form_instance->renderElement('tab_controls', '', $data) !!}
@unless(empty($data['form_elements']))
    @foreach ($data['form_elements'] as $key => $formElement)
        {!! $form_instance->renderElement(data_get($formElement, 'html_element'), $key, $formElement, $data); !!}
    @endforeach
@endunless