@php
    /**
     * @var bool $visible maybe always true because we are here
     * @var bool $disabled enabled or disabled
     * @var bool $read_only disallow edit
     * @var bool $auto_complete auto fill user inputs
     * @var string $name name attribute
     * @var string $label label of this element
     * @var mixed $value value attribute
     * @var mixed $default default value
     * @var bool $read_only
     * @var string $description
     * @var string $css_classes
     * @var string $css_group
     * @var string $x_model optional for alpine.js
     * @var string $livewire
     * @var array $html_data data attributes
     * @var array $options
     * @var array $x_data
     * @var int $element_index
     */

    use Modules\SystemBase\app\Services\ThemeService;

    /** @var ThemeService $themeService */
    $themeService = app(ThemeService::class);

    $folder = data_get($options, 'path', '');
    $directoryDeep = (int)data_get($options, 'directory_deep', 0);
    $regexWhitelist = data_get($options, 'regex_whitelist', []);
    $regexBlacklist = data_get($options, 'regex_blacklist', []);
    $addDelimiters = data_get($options, 'add_delimiters', '');
    $files = $themeService->getFilesFromTheme($folder, '', $directoryDeep, $regexWhitelist, $regexBlacklist, $addDelimiters);

@endphp
@if ($files)
    @include('form::components.form.select', ['options' => $files])
@else
    [No path in $folder: {{ $folder }}]
@endif
