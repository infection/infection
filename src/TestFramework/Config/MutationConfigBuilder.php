<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);

namespace Infection\TestFramework\Config;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Coverage\CodeCoverageData;

abstract class MutationConfigBuilder
{
    abstract public function build(Mutant $mutant) : string;

    protected function getInterceptorFileContent(string $interceptorPath, string $originalFilePath, string $mutatedFilePath)
    {
        $infectionPhar = '';

        if (0 === strpos(__FILE__, 'phar:')) {
            $infectionPhar = sprintf(
                '\Phar::loadPhar("%s", "%s");',
                str_replace('phar://', '', \Phar::running()),
                'infection.phar'
            );
        }

        return <<<CONTENT
{$infectionPhar}
require_once '{$interceptorPath}';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('{$originalFilePath}', '{$mutatedFilePath}');
IncludeInterceptor::enable();
CONTENT;
    }
}