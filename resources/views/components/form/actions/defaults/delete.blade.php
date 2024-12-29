@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $acceptLabel
     */

    $itemId = $this->formObjectId;

@endphp
@if ($itemId)
    <button x-on:click="messageBox.show('__default__.data-table.delete', {'delete-item': {livewire_id: '{{ $this->getId() }}', name: '{{ $this->getName() }}', item_id: {{ $itemId }}}})"
            type="button"
            class="btn btn-danger form-action-delete">{{ $acceptLabel ?? __("Delete") }}</button>
@endif
