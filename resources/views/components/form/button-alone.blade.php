@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $_liveWireAttr = '';
    if ($data['livewire']) {
        $_liveWireAttr = 'wire:click="'.$data['livewire'].'.'.$data['name'].'"';
    }
@endphp
<button class="form-control {{ $data['css_classes'] }}"
        name="{{ $data['name'] }}"
        type="button"
        @if($_liveWireAttr) {!! $_liveWireAttr !!} @endif
        @if($data['livewire_click']) wire:click="{!! $data['livewire_click'] !!}" @endif
>@if(!empty($data['bs_icon'])) <span class="bi bi-{{ $data['bs_icon'] }}"></span> @endif {{ $data['label'] }}</button>
