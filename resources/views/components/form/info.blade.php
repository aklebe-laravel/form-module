@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $_shouldValidateBool = (is_array($data['validator']) && (in_array('bool', $data['validator'])) ? true : (($data['validator'] === 'bool') ? true : false));
    $_formattedValue = $data['value'];
    if (is_bool($data['value']) || $_shouldValidateBool) {
        $_formattedValue = $data['value'] ? __('Yes') : __('No');
        $data['css_group'] .= $data['value'] ? ' alert alert-danger' : ' alert alert-success';
    } elseif (is_array($data['value']) || is_object($data['value'])) {
        $_formattedValue = print_r($data['value'], true);
    }
    // $_formattedValue = print_r($validator, true);
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    <div class="form-control-info {{ $data['css_classes'] }}">{!! $_formattedValue !!}</div>
    @include('form::components.form.element-parts.description')
</div>