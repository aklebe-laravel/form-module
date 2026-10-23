<?php
declare(strict_types = 1);

namespace Modules\Form\app\Listeners;

use Modules\Form\app\Services\MakeFormsService;

class DeployEnvFormer
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(\Modules\DeployEnv\app\Events\DeployEnvFormer $event): void
    {
        $makeFormService = app(MakeFormsService::class);
        foreach ($event->classes as $class) {
            $makeFormService->makeForm($event->moduleName, $class);
        }
    }
}
