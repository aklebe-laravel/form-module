<?php

namespace Modules\Form\app\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\DeployEnv\app\Events\DeployEnvFormer;
use Modules\Form\app\Events\BeforeRenderForm;
use Modules\Form\app\Events\FinalFormElements;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        DeployEnvFormer::class  => [
            \Modules\Form\app\Listeners\DeployEnvFormer::class,
        ],
        BeforeRenderForm::class => [
            \Modules\Form\app\Listeners\BeforeRenderForm::class,
        ],
        FinalFormElements::class => [
            \Modules\Form\app\Listeners\FinalFormElements::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
