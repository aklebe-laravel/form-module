<?php

namespace Modules\Form\app\Services;

use Modules\DeployEnv\app\Services\MakeModuleService;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\SystemBase\app\Services\ModuleService;

class MakeFormsService extends BaseService
{
    /**
     * @param  string  $moduleName
     * @param  string  $formName
     * @return bool
     * @throws \Exception
     */
    public function makeForm(string $moduleName, string $formName): bool
    {
        // get stubs path
        /** @var MakeModuleService $makeModuleService */
        $makeModuleService = app(MakeModuleService::class);
        $pathRootTemplate = ModuleService::getPath('module-stubs/FormTemplate', 'Form', 'resources');

        // adjust the parser placeholders
        $makeModuleService->additionalParserPlaceHolders['class_name'] = [
            'parameters' => [],
            'callback'   => function (array $placeholderParameters, array $parameters, array $recursiveData) use (
                $formName
            ) {
                return $formName;
            },
        ];

        // generate the files
        if ($makeModuleService->generateModuleFiles($moduleName, true, $pathRootTemplate)) {
            // $this->info("Form files successful generated!");
        } else {
            $this->error("Form files failed!");
            return false;
        }

        return true;
    }
}