@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $acceptLabel
     */

    $itemId = $this->formObjectId;

    $messageBoxParamsDelete = [
        'delete-item' => [
            'livewireId' => $this->getId(),
            'name' => $this->getName(),
            'itemId' => $itemId,
        ],
    ];
@endphp
@if ($itemId)
    <button x-on:click="messageBox.show('__default__.data-table.delete', {{ json_encode($messageBoxParamsDelete) }} )"
            type="button"
            class="btn btn-danger form-action-delete">{{ $acceptLabel ?? __("Delete") }}</button>
@endif
