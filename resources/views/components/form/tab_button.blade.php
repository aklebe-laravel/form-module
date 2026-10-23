@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $tabPageName = 'tab' . $data['tabPageIndex'];
@endphp
<button type="button" class="tab-button px-4 py-2 text-sm font-medium transition-colors duration-200
            @if($data['tabPageIndex'] == data_get($form_instance->activeTabs, $data['tabControlName'], 0)) active @else inactive @endif
            hover:text-secondary-700 hover:border-secondary-800
            focus:outline-none @if($data['disabled']) disabled @endif"
        @click="select('{{ $tabPageName }}')"
        :class="{ 'active' : (activeTab == '{{ $tabPageName }}'), 'inactive' : (activeTab != '{{ $tabPageName }}') }"
        id="{{ $data['tabControlName'] }}-{{ $tabPageName }}-tab"
        role="tab"
        @if($data['livewire'] ?? null) wire:ignore.self @endif >{{ data_get($data['tab'], 'label') }}</button>
