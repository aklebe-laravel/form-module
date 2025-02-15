@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $buttonLabel
     * @var string $buttonClick
     * @var string $buttonCss
     */
@endphp
{{-- Button becomes 100% width for small media --}}
<span class="d-flex d-md-inline-block">
    <button wire:click="{{ $buttonClick }}" type="button" class="btn w-100 mb-1 {{ $buttonCss }}">{{ $buttonLabel }}</button>
</span>
