@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $tabPageName = 'tab' . $data['tabPageIndex'];
    $tabDescription = data_get($data['content'], 'description');
@endphp
<div class="tab-pane"
     x-show="activeTab === '{{ $tabPageName }}'"
     {{-- Hide inactive pages before Alpine has started (no flicker, lazy content stays unloaded) --}}
     @if($data['tabPageIndex'] != 0) style="display: none;" @endif
     id="{{ $data['tabControlName'] }}-{{ $tabPageName }}-content" role="tabpanel"
     aria-labelledby="{{ $data['tabControlName'] }}-{{ $tabPageName }}-tab"
     {{-- Keep Alpine's visibility state of this pane on Livewire updates (children are still morphed) --}}
     wire:ignore.self
>
    {{--When tab is disabled, tab buttons can't be pressed, so we don't need to render the content--}}
    @if(!$data['disabled'])
        @if($tabDescription)
            <div class="alert alert-dark">{{ $tabDescription }}</div>
        @endif
        <div class="form-group-container">
            {!! $form_instance->renderElement('full_form', '', $data['content'], $data); !!}
        </div>
    @endif
</div>