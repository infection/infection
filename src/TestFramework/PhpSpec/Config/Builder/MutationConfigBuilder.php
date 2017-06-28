<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Config\Builder;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\MutationYamlConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Symfony\Component\Yaml\Yaml;

class MutationConfigBuilder implements ConfigBuilder
{
    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * @var string
     */
    private $originalYamlConfigPath;
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(string $tempDirectory, string $originalYamlConfigPath, string $projectDir)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalYamlConfigPath = $originalYamlConfigPath;
        $this->projectDir = $projectDir;
    }

    public function build(Mutant $mutant): string
    {
        $customAutoloadFilePath = sprintf(
            '%s/interceptor.phpspec.autoload.%s.infection.php',
            $this->tempDirectory,
            $mutant->getMutation()->getHash()
        );

        $parsedYaml = Yaml::parse(file_get_contents($this->originalYamlConfigPath));

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutant, $parsedYaml));

        $yamlConfiguration = new MutationYamlConfiguration(
            $this->tempDirectory,
            $parsedYaml,
            $customAutoloadFilePath
        );

        $newYaml = $yamlConfiguration->getYaml();

        $path = $this->buildPath($mutant);

        file_put_contents($path, $newYaml);

        return $path;
    }

    private function createCustomAutoloadWithInterceptor(Mutant $mutant, array $parsedYaml) : string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();

        $originalBootstrap = $this->getOriginalBootstrapFilePath($parsedYaml);
        $autoloadPlaceholder = $originalBootstrap ? "require_once '{$originalBootstrap}'" : '';
        $interceptorPath = dirname(__DIR__, 4) .  '/StreamWrapper/IncludeInterceptor.php';

        $customAutoload = <<<AUTOLOAD
<?php

%s
require_once '{$interceptorPath}';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('{$originalFilePath}', '{$mutatedFilePath}');
IncludeInterceptor::enable();

AUTOLOAD;

        return sprintf($customAutoload, $autoloadPlaceholder);
    }

    private function buildPath(Mutant $mutant): string
    {
        $fileName = sprintf('phpspecConfiguration.%s.infection.yml', $mutant->getMutation()->getHash());

        return $this->tempDirectory . '/' . $fileName;
    }

    /**
     * @param array $parsedYaml
     * @return string|null
     */
    private function getOriginalBootstrapFilePath(array $parsedYaml)
    {
        if (!array_key_exists('bootstrap', $parsedYaml)) {
            return null;
        }

        return sprintf('%s/%s', $this->projectDir, $parsedYaml['bootstrap']);
    }
}