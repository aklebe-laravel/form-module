@php
    use Modules\WebsiteBase\app\Models\Country;

    $ttlDefault = config('system-base.cache.default_ttl', 1);
    $options = Cache::remember('form_element.select_country.options', $ttlDefault, function () {
        return app('system_base')->toHtmlSelectOptions(
            Country::orderBy('nice_name', 'ASC')->selectRaw('*, LOWER(iso) as iso')->get(),
            ['nice_name', 'iso'],
            'iso',
            app('system_base')->getHtmlSelectOptionNoValue('No choice')
            );
        });

@endphp
@include('form::components.form.select', ['cmpCi' => true, 'options' => $options])
