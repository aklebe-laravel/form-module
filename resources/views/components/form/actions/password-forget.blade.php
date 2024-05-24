@php
    /**
     * @var \Modules\Form\app\Http\Livewire\Form\Base\ModelBase $this
     * @var Illuminate\Database\Eloquent\Model $editFormModelObject
     * @var string $acceptLabel
     */
    $acceptLabel = __('Email Password Reset Link');
@endphp
@include('form::components.form.actions.defaults.accept')