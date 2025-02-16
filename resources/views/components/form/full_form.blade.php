@php
    use Modules\Form\app\Forms\Base\NativeObjectBase;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * @var string $title
     * @var array $tab_controls
     * @var string $livewire
     * @var NativeObjectBase $form_instance
     * @var NativeObjectBaseLivewire $form_livewire
     **/

    $parentInheritVars = get_defined_vars();
@endphp
{!! $form_instance->renderElement('tab_controls', '', $parentInheritVars) !!}
@unless(empty($form_elements))
    @foreach ($form_elements as $key => $formElement)
        {!! $form_instance->renderElement(data_get($formElement, 'html_element'), $key, $formElement, $parentInheritVars); !!}
    @endforeach
@endunless