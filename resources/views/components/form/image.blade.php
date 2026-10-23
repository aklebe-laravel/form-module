@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $xModelName = (($data['x_model']) ? ($data['x_model'] . '.' . $data['name']) : '');
@endphp
<div class="form-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    <div class="image-box">
        @if($data['value'])
            <img
                @if($xModelName) :src="{{ $xModelName }}" @else src="{{ $data['value'] }}" @endif
                alt="{{ $data['value'] }}"
            >
        @else
            <div class="fake-img min-h-60 bg-surface-200">
                <span class="alert alert-warning">{{ __('No Image') }}</span>
            </div>
        @endif
    </div>
</div>