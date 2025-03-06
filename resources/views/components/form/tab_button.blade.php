@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $tabPageName = 'tab' . $data['tabPageIndex'];
@endphp
<li class="nav-item">
    <button type="button" class="nav-link w-100 @if($data['tabPageIndex'] == data_get($form_instance->activeTabs, $data['tabControlName'], 0)) active @endif @if($data['disabled']) disabled @endif" id="{{ $data['tabControlName'] }}-{{ $tabPageName }}-tab"
{{--            wire:click="$dispatchSelf('switch-tab', {'tabControl':'{{ $tabControlName }}', 'tabPage':'{{ $data['tabPageIndex'] }}'})"--}}
            data-bs-toggle="tab"
            data-bs-target="#{{ $data['tabControlName'] }}-{{ $tabPageName }}-content"
            aria-controls="{{ $data['tabControlName'] }}-{{ $tabPageName }}-content"
            aria-selected="false"
            role="tab"
            @if($data['livewire'] ?? null) wire:ignore.self @endif >{{ data_get($data['tab'], 'label') }}</button>

</li>
