@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $buttonLabel
     */
@endphp
@include('form::components.form.actions.defaults.default-button',[
    'buttonLabel' => $buttonLabel ?? __("Accept"),
    'buttonClick' => $this->getDefaultWireFormAccept(),
    'buttonCss' => 'btn-primary form-action-accept',
])
