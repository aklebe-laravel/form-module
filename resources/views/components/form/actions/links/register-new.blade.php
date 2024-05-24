@php
    /**
     * @var \Modules\Form\app\Http\Livewire\Form\Base\ModelBase $this
     * @var Illuminate\Database\Eloquent\Model $editFormModelObject
     * @var string $linkName
     * @var string $linkAddress
     */
    $linkName = __('Register');
    $linkAddress = route('register');
@endphp
@include('form::components.form.actions.defaults.link')