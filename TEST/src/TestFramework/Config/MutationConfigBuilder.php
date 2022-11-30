<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Config;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use Phar;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_replace;
use function str_starts_with;
use function strstr;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
abstract class MutationConfigBuilder
{
    public abstract function build(array $tests, string $mutantFilePath, string $mutationHash, string $mutationOriginalFilePath, string $version) : string;
    protected function getInterceptorFileContent(string $interceptorPath, string $originalFilePath, string $mutantFilePath) : string
    {
        $infectionPhar = '';
        if (str_starts_with(__FILE__, 'phar:')) {
            $infectionPhar = sprintf('\\Phar::loadPhar("%s", "%s");', str_replace('phar://', '', Phar::running(\true)), 'infection.phar');
        }
        $namespacePrefix = $this->getInterceptorNamespacePrefix();
        return <<<CONTENT
{$infectionPhar}
require_once '{$interceptorPath}';

use {$namespacePrefix}Infection\\StreamWrapper\\IncludeInterceptor;

IncludeInterceptor::intercept('{$originalFilePath}', '{$mutantFilePath}');
IncludeInterceptor::enable();
CONTENT;
    }
    private function getInterceptorNamespacePrefix() : string
    {
        $prefix = strstr(__NAMESPACE__, 'Infection', \true);
        Assert::string($prefix);
        return $prefix;
    }
}
