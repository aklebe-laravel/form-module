@php
    use Modules\Form\app\Forms\Base\NativeObjectBase;
    use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

    /** @var ModelBase $this */
    $_v = array_merge(NativeObjectBase::defaultViewData, [
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
