@php
    use Modules\Form\app\Forms\Base\NativeObjectBase;
    use Modules\WebsiteBase\app\Models\Store;

    $ttlDefault = config('system-base.cache.default_ttl', 1);
    $options = Cache::remember('form_element.select_country.options', $ttlDefault, function () {
        return app('system_base')->toHtmlSelectOptions(
            Store::orderBy('code','ASC')->get(),
            ['id','code'],
            'id',
            app('system_base')->getHtmlSelectOptionNoValue('No choice', NativeObjectBase::UNSELECT_RELATION_IDENT)
            );
        });

@endphp
@include('form::components.form.select', ['cmpCi' => true, 'options' => $options])
