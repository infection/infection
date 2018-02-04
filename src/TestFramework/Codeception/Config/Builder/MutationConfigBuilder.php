<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Config\Builder;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Codeception\Config\MutationYamlConfiguration;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;

class MutationConfigBuilder extends ConfigBuilder
{
    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * @var string
     */
    private $originalConfigPath;

    public function __construct(string $tempDirectory, string $projectDirectory, string $originalConfigPath)
    {
        $this->tempDirectory = $tempDirectory;
        $this->projectDirectory = $projectDirectory;
        $this->originalConfigPath = $originalConfigPath;
    }

    public function build(Mutant $mutant): string
    {
        $_SERVER['CUSTOM_AUTOLOAD_FILE_PATH'] = $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tempDirectory,
            $mutant->getMutation()->getHash()
        );

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutant));

        return $this->originalConfigPath;
    }

    private function createCustomAutoloadWithInterceptor(Mutant $mutant): string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();
        $interceptorPath = dirname(__DIR__, 4) . '/StreamWrapper/IncludeInterceptor.php';

        $autoload = sprintf('%s/vendor/autoload.php', $this->projectDirectory);

        $customAutoload = <<<AUTOLOAD
<?php

require_once '{$autoload}';
%s

AUTOLOAD;

        return sprintf($customAutoload, $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutatedFilePath));
    }
}
