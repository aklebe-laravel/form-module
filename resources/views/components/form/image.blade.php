@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $xModelName = (($data['x_model']) ? ($data['x_model'] . '.' . $data['name']) : '');
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    <div class="container image-box">
        @if($data['value'])
            <img
                @if($xModelName) :src="{{ $xModelName }}" @else src="{{ $data['value'] }}" @endif
                alt="{{ $data['value'] }}"
            >
        @else
            <div class="bg-light text-danger p-4 fake-img">
                {{ __('No Image') }}
            </div>
        @endif
    </div>
</div>