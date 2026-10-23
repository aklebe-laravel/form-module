@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $linkName
     * @var string $linkAddress
     */
    $linkName = __('Forgot your password?');
    $linkAddress = route('password.request');
@endphp
@include('form::components.form.actions.defaults.link')