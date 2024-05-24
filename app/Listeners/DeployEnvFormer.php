<?php

namespace Modules\Form\app\Listeners;

use Illuminate\Support\Facades\Log;
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
        Log::debug(__METHOD__, [$event->moduleName, $event->classes]);

        $makeFormService = app(MakeFormsService::class);
        foreach ($event->classes as $class) {
            $makeFormService->makeForm($event->moduleName, $class);
        }
    }
}
