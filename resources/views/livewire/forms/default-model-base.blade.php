@php
    use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

    /** @var ModelBase $this */

    $_formErrors = [];
    if (!($editForm = $this->getFormResult())) {
        $_formErrors[] = 'Missing Form Result';
    }

    if (!($editFormModelObject = $this->dataTransfer)) {
        $_formErrors[] = 'Missing form data';
    }
@endphp
@include('form::livewire.forms.default-native-object')
