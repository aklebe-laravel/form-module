<?php

namespace Modules\Form\app\Providers;

use Modules\Form\app\Console\MakeForm;
use Modules\Form\app\Services\FormService;
use Modules\SystemBase\app\Providers\Base\ModuleBaseServiceProvider;

class FormServiceProvider extends ModuleBaseServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected string $moduleName = 'Form';

    /**
     * @var string $moduleNameLower
     */
    protected string $moduleNameLower = 'form';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        $this->commands([
            MakeForm::class,
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(FormService::class);

        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

}
