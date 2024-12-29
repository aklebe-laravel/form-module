@php
    use Modules\Form\app\Forms\Base\NativeObjectBase;

    /**
     * @var string $title
     * @var string $livewire
     * @var string $tabControlName
     * @var string $value
     * @var integer $element_index
     * @var integer $tabPageIndex location tab_controls.blade.php
     * @var NativeObjectBase $form_instance
     **/

    $tabPageName = 'tab' . $tabPageIndex;
    //dump($form_instance->activeTabs);
@endphp
<li class="nav-item" role="presentation">
    <button type="button" class="nav-link @if($tabPageIndex == data_get($form_instance->activeTabs, $tabControlName, 0)) active @endif @if($disabled) disabled @endif" id="{{ $tabControlName }}-{{ $tabPageName }}-tab"
{{--            wire:click="$dispatchSelf('switch-tab', {'tabControl':'{{ $tabControlName }}', 'tabPage':'{{ $tabPageIndex }}'})"--}}
            data-bs-toggle="tab"
            data-bs-target="#{{ $tabControlName }}-{{ $tabPageName }}-content" role="tab"
            aria-controls="{{ $tabControlName }}-{{ $tabPageName }}-content" aria-selected="true"
            @if($livewire ?? null) wire:ignore.self @endif >{{ data_get($tab, 'label') }}</button>

</li>
