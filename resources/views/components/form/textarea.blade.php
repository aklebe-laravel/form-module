@php
    /**
     * Providing edit a JSON if $value is an array or object.
     *
     *
     *
     * @var Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase $form_instance
     * @var array $data
     */
    $attrs = $form_instance->calcInputAttributes($data);

    // Extract wire:model if it exists
    $wireModel = $attrs->get('wire:model') ?? $attrs->get('wire:model.blur') ?? $attrs->get('wire:model.live');

    // Check if we are dealing with a complex object/array
    $isObject = !is_scalar($data['value'] ?? null) && !is_null($data['value'] ?? null);
@endphp
<div
        class="form-group {{ $data['css_group'] }}"
        @if($wireModel && $isObject)
            x-data="{
            wireData: @entangle($wireModel),
            jsonText: '',
            init() {
                this.jsonText = JSON.stringify(this.wireData, null, 2);
                this.$watch('wireData', value => this.jsonText = JSON.stringify(value, null, 2));
            },
            updateWire() {
                try {
                    this.wireData = this.jsonText.trim() ? JSON.parse(this.jsonText) : null;
                } catch (e) {
                    // Fail silently on syntax errors while typing
                }
            }
        }"
        @endif
>
    @include('form::components.form.element-parts.label')

    @if($wireModel && $isObject)
        {{-- JSON Array Textarea Object Bypass --}}
        <x-textarea
                :attributes="$attrs->except(['wire:model', 'wire:model.blur', 'wire:model.live'])"
                x-model="jsonText"
                @blur="updateWire()"
        ></x-textarea>
    @else
        {{-- Normal Textarea Fallback for string/null --}}
        <x-textarea :attributes="$attrs">
            {{ is_scalar($data['value']) ? $data['value'] : '' }}
        </x-textarea>
    @endif

    @include('form::components.form.element-parts.description')
</div>
