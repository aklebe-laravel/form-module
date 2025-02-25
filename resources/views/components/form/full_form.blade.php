@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * @var string $title
     * @var array $tab_controls
     * @var string $livewire
     * @var NativeObjectBaseLivewire $form_livewire
     **/

    $parentInheritVars = get_defined_vars();
@endphp
{!! $form_livewire->renderElement('tab_controls', '', $parentInheritVars) !!}
@unless(empty($form_elements))
    @foreach ($form_elements as $key => $formElement)
        {!! $form_livewire->renderElement(data_get($formElement, 'html_element'), $key, $formElement, $parentInheritVars); !!}
    @endforeach
@endunless