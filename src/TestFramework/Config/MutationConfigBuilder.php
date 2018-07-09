<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Config;

use Infection\Mutant\MutantInterface;

/**
 * @internal
 */
abstract class MutationConfigBuilder
{
    abstract public function build(MutantInterface $mutant): string;

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
