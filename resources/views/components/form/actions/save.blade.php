@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $buttonLabel
     */
    $buttonLabel = __('Save');
@endphp
@include('form::components.form.actions.defaults.accept')