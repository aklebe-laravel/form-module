@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $buttonLabel
     * @var string $buttonClick
     * @var string $buttonCss
     * @var string $buttonType 'livewire' (wire:click), 'alpine' (x-on:click) or 'default' (standard js)
     */

    $buttonType = $buttonType ?? 'livewire';
@endphp
{{-- Button becomes 100% width for small media --}}
<span class="d-flex d-md-inline-block">
    <button type="button"
            @if($buttonType === 'alpine') x-on:click="{{ $buttonClick }}" @endif
            @if($buttonType === 'livewire') wire:click="{{ $buttonClick }}" @endif
            @if($buttonType === 'default') onclick="{{ $buttonClick }}" @endif
            class="btn w-100 mb-1 {{ $buttonCss }}">{{ $buttonLabel }}</button>
</span>
