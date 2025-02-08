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
    @include('form::components.form.actions.defaults.default-button',[
        'buttonLabel' => $acceptLabel ?? __("Delete"),
        'buttonClick' => "messageBox.show('__default__.data-table.delete', ".json_encode($messageBoxParamsDelete).")",
        'buttonCss' => 'btn-danger form-action-delete',
    ])
@endif
