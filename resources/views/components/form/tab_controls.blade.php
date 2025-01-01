@php
    use Modules\Form\app\Forms\Base\NativeObjectBase;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * @var string    $title
     * @var array     $tab_controls
     * @var string    $livewire
     * @var NativeObjectBase $form_instance
     * @var NativeObjectBaseLivewire $form_livewire
     **/
@endphp
@if(!empty($tab_controls))
    @foreach($tab_controls as $tabControlName => $tabControl)
        @php
            $tabControlName = uniqid('tc'); // @todo: unconditionally?
            $tabControl = $form_instance->prepareFormViewData('tab_control', $tabControlName, $tabControl, get_defined_vars());
        @endphp

        <ul class="nav nav-tabs" id="{{ uniqid('tab_list_') }}" role="tablist">
            @php $tabPageIndex = 0; @endphp
            @foreach($tabControl['tab_pages'] as $tabPageName => $tabPage)
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
            @foreach($tabControl['tab_pages'] as $tabPageName => $tabPage)
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

    @endforeach
@endif