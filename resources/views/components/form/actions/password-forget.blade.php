@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $acceptLabel
     */
    $acceptLabel = __('Email Password Reset Link');
@endphp
@include('form::components.form.actions.defaults.accept')