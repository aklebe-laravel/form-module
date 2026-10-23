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
    $lucideIcon = "lucide-{$data['icon']}";
@endphp
<x-button class="form-control {{ $data['css_classes'] }}"
          name="{{ $data['name'] }}"
          type="button"
          @if($_liveWireAttr)
              {!! $_liveWireAttr !!}
          @endif
          @if($data['livewire_click']) wire:click="{!! $data['livewire_click'] !!}" @endif
>@if(!empty($data['icon']))
        <x-dynamic-component :component="$lucideIcon" class="h-5 w-5"/>
    @endif {{ $data['label'] }}</x-button>
