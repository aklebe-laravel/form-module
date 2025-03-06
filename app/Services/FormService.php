<?php

namespace Modules\Form\app\Services;

use Closure;
use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\SystemBase\app\Services\CacheService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\SystemBase\app\Services\ThemeService;

class FormService extends BaseService
{
    /**
     * should be indexed by attribute codes or config paths
     *
     * @var array
     */
    protected array $formElements = [];

    /**
     * @param  string   $key
     * @param  Closure  $formElementData
     *
     * @return void
     */
    public function registerFormElement(string $key, Closure $formElementData): void
    {
        $this->formElements[$key] = $formElementData;
    }

    /**
     * @param  string  $key
     *
     * @return Closure|null
     */
    public function getFormElementClosure(string $key): ?Closure
    {
        if (!isset($this->formElements[$key]) || (!$this->formElements[$key] instanceof Closure)) {
            return null;
        }

        return $this->formElements[$key];
    }

    /**
     * @param  string  $key
     * @param  array   $mergeData
     *
     * @return array
     */
    public function getFormElement(string $key, array $mergeData = []): array
    {
        $x = $this->getFormElementClosure($key);
        if ($x) {
            return $x($mergeData);
        }

        return $mergeData;
    }

    /**
     * @return array
     */
    public static function getFormElementFormViewModeOptions(): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_form_view_mode.options', function () {
            return [
                NativeObjectBase::viewModeSimple   => __('Simple'),
                NativeObjectBase::viewModeDefault  => __('Default'),
                NativeObjectBase::viewModeExtended => __('Extended'),
            ];
        });
    }

    /**
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementFormViewMode(array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element'      => 'select',
            'options'           => static::getFormElementFormViewModeOptions(),
            'name'              => 'controls_set_view_mode',
            'livewire'          => 'liveCommands',
            'livewire_live'     => true,
            'livewire_debounce' => 200,
        ], $mergeData);
    }

    /**
     * @param  array  $themeOptions
     *
     * @return array
     */
    public static function getFormElementFormThemeFileOptions(array $themeOptions): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_form_theme_file.options', function () use ($themeOptions) {
            /** @var ThemeService $themeService */
            $themeService = app(ThemeService::class);
            /** @var SystemService $systemService */
            $systemService = app('system_base');

            $folder = data_get($themeOptions, 'path', '');

            $directoryDeep = (int) data_get($themeOptions, 'directory_deep', 0);
            $regexWhitelist = data_get($themeOptions, 'regex_whitelist', []);
            $regexBlacklist = data_get($themeOptions, 'regex_blacklist', []);
            $addDelimiters = data_get($themeOptions, 'add_delimiters', '');

            $files = $themeService->getFilesFromTheme($folder, '', $directoryDeep, $regexWhitelist, $regexBlacklist, $addDelimiters);

            return $systemService->toHtmlSelectOptions($files, null, '[key]', $systemService->selectOptionsSimple[$systemService::selectValueNoChoice], $systemService::SortModeByKey | $systemService::SortModeAsc);
        });
    }

    /**
     * @param  array  $themeOptions
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementFormThemeFile(array $themeOptions, array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'select',
            'options'      => static::getFormElementFormThemeFileOptions($themeOptions),
            'label'        => __('View File'),
            'description'  => __("Force this content if set."),
            'validator'    => [
                'nullable',
                'string',
                'Max:255',
            ],
            'css_group'    => 'col-12',
        ], $mergeData);
    }

}