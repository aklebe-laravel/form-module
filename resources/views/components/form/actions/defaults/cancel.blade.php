@php
    /**
     * @var \Modules\Form\app\Http\Livewire\Form\Base\ModelBase $this
     * @var Illuminate\Database\Eloquent\Model $editFormModelObject
     * @var string $cancelLabel
     */
@endphp
<button wire:click="{{ $this->getDefaultWireFormCancel() }}" type="button"
        class="btn btn-outline-secondary">{{ $cancelLabel ?? __("Cancel") }}</button>