@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */
@endphp
<div class="form-group form-label-group {{ $data['css_group'] }}">
    @include('form::components.form.element-parts.label')
    <select {!! $form_instance->calcInputAttributesString($data) !!}>
        @unless(empty($data['options']))
            @foreach($data['options'] as $k => $v)
                @if(isset($data['cmpCi']) && $data['cmpCi'])
                    {{--@todo: extra logic like strCaseCompare() will be ignored here if wire is enabled above--}}
                    <option
                            @if(!$data['x_model'] && app('system_base')->strCaseCompare($k, $data['value'])) selected="selected" @endif
                    value="{{ $k }}"
                            @if(($k != $data['value']) && ($data['disabled'] || $data['read_only'])) disabled="disabled" @endif
                    >{{ $v }}</option>
                @else
                    @php
                        $_valueHit = (is_array($data['value']) || is_object($data['value'])) ? (in_array($k, (array)$data['value'])) : ($k == $data['value']);
                    @endphp
                    <option
                            @if((!$data['x_model']) && ($_valueHit)) selected="selected" @endif
                    value="{{ $k }}"
                            @if((!$_valueHit) && ($data['disabled'] || $data['read_only'])) disabled="disabled" @endif
                    >{{ $v }}</option>
                @endif
            @endforeach
        @endunless
    </select>
    @include('form::components.form.element-parts.description')
</div>