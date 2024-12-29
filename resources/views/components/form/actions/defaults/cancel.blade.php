@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $cancelLabel
     */
@endphp
<button wire:click="{{ $this->getDefaultWireFormCancel() }}" type="button"
        class="btn btn-outline-secondary form-action-cancel">{{ $cancelLabel ?? __("Cancel") }}</button>