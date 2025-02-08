@php
    use Modules\Form\app\Forms\Base\NativeObjectBase;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase as NativeObjectBaseLivewire;

    /**
     * @var string $title
     * @var string $livewire
     * @var string $tabControlName
     * @var string $value
     * @var integer $element_index
     * @var integer $tabPageIndex location tab_controls.blade.php
     * @var NativeObjectBase $form_instance
     * @var NativeObjectBaseLivewire $form_livewire
     **/

    $tabPageName = 'tab' . $tabPageIndex;
@endphp
<li class="nav-item">
    <button type="button" class="nav-link w-100 @if($tabPageIndex == data_get($form_livewire->activeTabs, $tabControlName, 0)) active @endif @if($disabled) disabled @endif" id="{{ $tabControlName }}-{{ $tabPageName }}-tab"
{{--            wire:click="$dispatchSelf('switch-tab', {'tabControl':'{{ $tabControlName }}', 'tabPage':'{{ $tabPageIndex }}'})"--}}
            data-bs-toggle="tab"
            data-bs-target="#{{ $tabControlName }}-{{ $tabPageName }}-content"
            aria-controls="{{ $tabControlName }}-{{ $tabPageName }}-content"
            aria-selected="false"
            role="tab"
            @if($livewire ?? null) wire:ignore.self @endif >{{ data_get($tab, 'label') }}</button>

</li>
