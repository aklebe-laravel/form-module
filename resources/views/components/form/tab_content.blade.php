@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $tabPageName = 'tab' . $data['tabPageIndex'];
    $tabDescription = data_get($data['content'], 'description');
@endphp
<div class="tab-pane show @if($data['tabPageIndex'] == 0) active @endif"
     id="{{ $data['tabControlName'] }}-{{ $tabPageName }}-content" role="tabpanel"
     aria-labelledby="{{ $data['tabControlName'] }}-{{ $tabPageName }}-tab" @if($livewire ?? null) wire:ignore.self @endif
>
    {{--When tab is disabled, tab buttons can't be pressed, so we don't need to render the content--}}
    @if(!$data['disabled'])
        <div class="container">
            @if($tabDescription)
                <div class="row">
                    <div class="col-12 alert alert-dark">{{ $tabDescription }}</div>
                </div>
            @endif
            <div class="row">
                {!! $form_instance->renderElement('full_form', '', $data['content'], $data); !!}
            </div>
        </div>
    @endif
</div>