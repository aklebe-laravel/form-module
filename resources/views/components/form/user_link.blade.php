@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    <a class="link-secondary {{ $data['css_classes'] }}"
            href="{{ route('user-profile', $data['value']) }}"
    >{{ $data['label'] }}</a>
    @include('form::components.form.element-parts.description')
</div>