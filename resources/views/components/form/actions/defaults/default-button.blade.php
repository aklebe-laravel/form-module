@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $buttonLabel
     * @var string $buttonClick
     * @var string $buttonCss
     * @var string $buttonColor
     * @var string $buttonType 'livewire' (wire:click), 'alpine' (x-on:click) or 'default' (standard js)
     */

    $attrs = [
        'type' => 'button',
        'class' => $buttonCss,
    ];

    $buttonColor ??= 'secondary';
    $buttonType ??= 'livewire';
    if($buttonType === 'alpine') $attrs['x-on:click']=$buttonClick;
    if($buttonType === 'livewire') $attrs['wire:click']= $buttonClick;
    if($buttonType === 'default') $attrs['onclick']=$buttonClick;
    $attrs = new \Illuminate\View\ComponentAttributeBag($attrs);
    $buttonType = $buttonType ?? 'livewire';
@endphp
{{-- Button becomes 100% width for small media --}}
<span class="justify-end">
    <x-button :attributes="$attrs" :color="$buttonColor">{{ $buttonLabel }}</x-button>
</span>
