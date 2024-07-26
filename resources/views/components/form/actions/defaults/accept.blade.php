@php
    /**
     * @var \Modules\Form\app\Http\Livewire\Form\Base\ModelBase $this
     * @var Illuminate\Database\Eloquent\Model $editFormModelObject
     * @var string $acceptLabel
     */
@endphp
<button wire:click="{{ $this->getDefaultWireFormAccept() }}" type="button"
        class="btn btn-primary form-action-accept">{{ $acceptLabel ?? __("Accept") }}</button>