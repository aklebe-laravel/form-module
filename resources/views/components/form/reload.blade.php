@php
    use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /** @var ModelBase $this */
    $_v = array_merge($this->getFormInstance()->defaultViewData, [
        'name' => 'controls.reload',
        'label' => '',
        'bs_icon' => 'box-arrow-in-down',
        'css_classes' => 'btn btn-danger w-auto',
        'css_group' => 'w-auto',
        'livewire_click' => '$dispatchSelf(\'updating\', {\'property\':\'controls.reload\',\'value\':true})',
        'livewire_debounce' => 100,
    ]);
@endphp
@include('form::components.form.button-alone', $_v)
