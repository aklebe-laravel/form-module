<?php

namespace Modules\Form\app\Console;

use Exception;
use Illuminate\Console\Command;
use Modules\DeployEnv\app\Console\Base\DeployEnvBase;
use Modules\Form\app\Services\MakeFormsService;
use Modules\SystemBase\app\Services\ModuleService;

class MakeForm extends DeployEnvBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'form:make {form_name} {module_name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create form relevant classes for module inclusive datatable';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle()
    {
        if (!($formName = $this->argument('form_name'))) {
            $this->error("Missing form name!");
            return Command::FAILURE;
        }
        $moduleName = $this->argument('module_name');

        $moduleName = ModuleService::getStudlyName($moduleName);
        $formName = ModuleService::getStudlyName($formName);

        $this->info(sprintf("Form %s::%s", $moduleName, $formName));

        $makeFormService = app(MakeFormsService::class);
        return $makeFormService->makeForm($moduleName, $formName) ? Command::SUCCESS : Command::FAILURE;
    }

}
