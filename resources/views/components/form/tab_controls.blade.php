@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * @var NativeObjectBaseLivewire $form_instance
     * @var array $data
     **/
    $default = 'tab0';
@endphp
@if(!empty($data['tab_controls']))

    @foreach($data['tab_controls'] as $tabControlName => $tabControl)
        @php
            $tabControlName = $form_instance->getStableKey('tab-control-');
            $tabControl = $form_instance->prepareFormViewData('tab_control', $tabControlName, $tabControl, $data);
            $tabControlPages = collect($tabControl['tab_pages'])->where('visible', true);
            $tabControlPagesCount = $tabControlPages->count();
        @endphp

        <div class="responsive-tabs w-full mx-auto mt-10"
             x-data="{
                    activeTab: '{{ $default }}',
                    transitioning: false,
                    select(tab) {
                        if (this.activeTab === tab) return;
                        this.activeTab = tab;
                        //console.log('new tab: ' + this.activeTab);
                    }
                }">

            {{-- If only one tab page, remove tabcontrol --}}
            @if($tabControlPagesCount === 1)
                @php
                    $tabPage = $tabControlPages->first();
                    $tabPage['tabPageIndex'] = 0;
                    $tabPage['tabControlName'] = $tabControlName;
                @endphp
                {!! $form_instance->renderElement('tab_content', '', $tabPage, $tabControl) !!}
            @else

                <div class="flex border-b border-gray-200" role="tablist" id="{{ $form_instance->getStableKey('tab-control-tab-list') }}">

                    @php $tabPageIndex = 0; @endphp
                    @foreach($tabControlPages as $tabPageName => $tabPage)
                        @php
                            if (!$tabPage) {
                                continue;
                            }
                            $tabPage['tabPageIndex'] = $tabPageIndex;
                            $tabPage['tabControlName'] = $tabControlName;
                            $tabPageIndexCode = 'tab' . $tabPageIndex;
                        @endphp
                        {!! $form_instance->renderElement('tab_button', $tabPageName, $tabPage, $tabControl) !!}
                        @php $tabPageIndex++; @endphp
                    @endforeach
                </div>
                <div class="mt-4 rounded-lg ">
                    @php $tabPageIndex = 0; @endphp
                    @foreach($tabControlPages as $tabPageName => $tabPage)
                        @php
                            if (!$tabPage) {
                                continue;
                            }
                            $tabPage['tabPageIndex'] = $tabPageIndex;
                            $tabPage['tabControlName'] = $tabControlName;
                            $tabPageIndexCode = 'tab' . $tabPageIndex;
                        @endphp
                        {!! $form_instance->renderElement('tab_content', $tabPageName, $tabPage, $tabControl) !!}
                        @php $tabPageIndex++; @endphp
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
@endif