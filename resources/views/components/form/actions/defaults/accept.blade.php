@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $acceptLabel
     */
@endphp
<button wire:click="{{ $this->getDefaultWireFormAccept() }}" type="button"
        class="btn btn-primary form-action-accept">{{ $acceptLabel ?? __("Accept") }}</button>