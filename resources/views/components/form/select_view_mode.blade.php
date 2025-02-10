@php
    use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /** @var ModelBase $this */
    $_v = array_merge($this->getFormInstance()->defaultViewData, [
        'options' => [
            NativeObjectBase::viewModeSimple => __('Simple'),
            NativeObjectBase::viewModeDefault => __('Default'),
            NativeObjectBase::viewModeExtended => __('Extended'),
        ],
        'name' => 'controls.set_view_mode',
        'livewire' => 'liveCommands',
        'livewire_live' => true,
        'livewire_debounce' => 200,
    ]);
@endphp
@include('form::components.form.select', $_v)
