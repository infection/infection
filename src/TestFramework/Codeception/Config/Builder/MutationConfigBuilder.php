<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Config\Builder;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Codeception\Config\YamlConfigurationHelper;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;

class MutationConfigBuilder extends ConfigBuilder
{
    /**
     * @var YamlConfigurationHelper
     */
    private $configurationHelper;

    public function __construct(string $tempDir, string $projectDir, string $originalConfig)
    {
        $this->configurationHelper = new YamlConfigurationHelper($tempDir, $projectDir, $originalConfig);
    }

    public function build(Mutant $mutant): string
    {
        $_SERVER['CUSTOM_AUTOLOAD_FILE_PATH'] = $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->configurationHelper->getTempDir(),
            $mutant->getMutation()->getHash()
        );

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutant));

        $pathToMutationConfigFile = $this->configurationHelper->getTempDir() . DIRECTORY_SEPARATOR . sprintf('codeception.%s.infection.xml', $mutant->getMutation()->getHash());

        file_put_contents($pathToMutationConfigFile, $this->configurationHelper->getTransformedConfig($mutant->getMutation()->getHash(), false));

        return $pathToMutationConfigFile;
    }

    private function createCustomAutoloadWithInterceptor(Mutant $mutant): string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();
        $interceptorPath = dirname(__DIR__, 4) . '/StreamWrapper/IncludeInterceptor.php';

        $autoload = sprintf('%s/vendor/autoload.php', $this->configurationHelper->getProjectDir());

        $customAutoload = <<<AUTOLOAD
<?php

require_once '{$autoload}';
%s

AUTOLOAD;

        return sprintf($customAutoload, $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutatedFilePath));
    }
}
