@php
    /**
     * @var \Modules\Form\app\Http\Livewire\Form\Base\ModelBase $this
     * @var Illuminate\Database\Eloquent\Model $editFormModelObject
     * @var string $acceptLabel
     */
    // dd(get_defined_vars());

    $itemId = $this->formObjectId;

@endphp
@if ($itemId)
    <button x-on:click="messageBox.show('__default__.data-table.delete', {'delete-item': {livewire_id: '{{ $this->getId() }}', name: '{{ $this->getName() }}', item_id: {{ $itemId }}}})"
            type="button"
            class="btn btn-danger form-action-delete">{{ $acceptLabel ?? __("Delete") }}</button>
@endif
