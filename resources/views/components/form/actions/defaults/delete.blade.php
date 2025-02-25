@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $buttonLabel
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
        'buttonType' => 'alpine',
        'buttonLabel' => $buttonLabel ?? __("Delete"),
        'buttonClick' => "messageBox.show('__default__.form.delete', ".json_encode($messageBoxParamsDelete).")",
        'buttonCss' => 'btn-danger form-action-delete',
    ])
@endif
