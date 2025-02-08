@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $cancelLabel
     */
@endphp
@include('form::components.form.actions.defaults.default-button',[
    'buttonLabel' => $cancelLabel ?? __("Cancel"),
    'buttonClick' => $this->getDefaultWireFormCancel(),
    'buttonCss' => 'btn-outline-secondary form-action-cancel',
])
