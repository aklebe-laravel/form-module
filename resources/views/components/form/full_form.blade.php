@php
    /**
     * @var string $title
     * @var array $tab_controls
     * @var string $livewire
     * @var \Modules\Form\app\Forms\Base\ModelBase $form_instance
     **/
//    dd(get_defined_vars());
    $parentInheritVars = get_defined_vars();
    // @todo: doesn't works but should (for all parentOptions like get_defined_vars())
//    $parentInheritVars = app('system_base')->arrayCopyWhitelisted($form_instance->defaultViewData, get_defined_vars(), $form_instance->inheritViewData);
@endphp
{!! $form_instance->renderElement('tab_controls', '', $parentInheritVars) !!}
@unless(empty($form_elements))
    @foreach ($form_elements as $key => $formElement)
        {!! $form_instance->renderElement(data_get($formElement, 'html_element'), $key, $formElement, $parentInheritVars); !!}
    @endforeach
@endunless