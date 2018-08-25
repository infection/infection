<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Config\Builder\Mutation;

use Infection\Config\InfectionConfig;
use Infection\TestFramework\Config\Builder\AbstractBuilder as Builder;

/**
 * @internal
 */
abstract class AbstractBuilder extends Builder implements BuilderInterface
{
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(
        InfectionConfig $infectionConfig,
        string $tempDirectory,
        string $configPath,
        string $projectDir
    )
    {
        $this->projectDir = $projectDir;

        parent::__construct($infectionConfig, $tempDirectory, $configPath);
    }

    protected function getProjectsDirectory(): string
    {
        return $this->projectDir;
    }

    protected function getInterceptorFileContent(string $interceptorPath, string $originalFilePath, string $mutatedFilePath): string
    {
        $infectionPhar = '';

        if (0 === strpos(__FILE__, 'phar:')) {
            $infectionPhar = sprintf(
                '\Phar::loadPhar("%s", "%s");',
                str_replace('phar://', '', \Phar::running(true)),
                'infection.phar'
            );
        }

        $namespacePrefix = $this->getInterceptorNamespacePrefix();

        return <<<CONTENT
{$infectionPhar}
require_once '{$interceptorPath}';

use {$namespacePrefix}Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('{$originalFilePath}', '{$mutatedFilePath}');
IncludeInterceptor::enable();
CONTENT;
    }

    private function getInterceptorNamespacePrefix(): string
    {
        $prefix = strstr(__NAMESPACE__, 'Infection', true);
        \assert(\is_string($prefix));

        return $prefix;
    }
}
