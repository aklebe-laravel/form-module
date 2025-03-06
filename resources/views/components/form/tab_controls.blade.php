@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * @var NativeObjectBaseLivewire $form_instance
     * @var array $data
     **/
@endphp
@if(!empty($data['tab_controls']))
    <div class="container responsive-tabs">
        @foreach($data['tab_controls'] as $tabControlName => $tabControl)
            @php
                $tabControlName = uniqid('tc'); // @todo: unconditionally?
                $tabControl = $form_instance->prepareFormViewData('tab_control', $tabControlName, $tabControl, $data);
                $tabControlPages = collect($tabControl['tab_pages'])->where('visible', true);
                $tabControlPagesCount = $tabControlPages->count();
            @endphp

            {{-- If only one tab page, remove tabcontrol --}}
            @if($tabControlPagesCount === 1)
                @php
                    $tabPage = $tabControlPages->first();
                    $tabPage['tabPageIndex'] = 0;
                    $tabPage['tabControlName'] = $tabControlName;
                @endphp
                {!! $form_instance->renderElement('tab_content', '', $tabPage, $tabControl) !!}
            @else
                <ul class="nav nav-tabs flex-column flex-md-row" id="{{ uniqid('tab_list_') }}" role="tablist">
                    @php $tabPageIndex = 0; @endphp
                    @foreach($tabControlPages as $tabPageName => $tabPage)
                        @php
                            if (!$tabPage) {
                                continue;
                            }
                            $tabPage['tabPageIndex'] = $tabPageIndex;
                            $tabPage['tabControlName'] = $tabControlName;
                        @endphp
                        {!! $form_instance->renderElement('tab_button', $tabPageName, $tabPage, $tabControl) !!}
                        @php $tabPageIndex++; @endphp
                    @endforeach
                </ul>
                <div class="tab-content tab-content-form-builder">
                    @php $tabPageIndex = 0; @endphp
                    @foreach($tabControlPages as $tabPageName => $tabPage)
                        @php
                            if (!$tabPage) {
                                continue;
                            }
                            $tabPage['tabPageIndex'] = $tabPageIndex;
                            $tabPage['tabControlName'] = $tabControlName;
                        @endphp
                        {!! $form_instance->renderElement('tab_content', $tabPageName, $tabPage, $tabControl) !!}
                        @php $tabPageIndex++; @endphp
                    @endforeach
                </div>
            @endif

        @endforeach
    </div>
@endif