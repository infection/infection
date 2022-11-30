<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config\Builder;

use function array_key_exists;
use function assert;
use function file_put_contents;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\StreamWrapper\IncludeInterceptor;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config\MutationYamlConfiguration;
use function is_string;
use Phar;
use function sprintf;
use function str_replace;
use function strpos;
use function strstr;
use _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Yaml;
class MutationConfigBuilder
{
    private string $tempDirectory;
    private string $originalYamlConfigPath;
    private string $projectDir;
    public function __construct(string $tempDirectory, string $originalYamlConfigPath, string $projectDir)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalYamlConfigPath = $originalYamlConfigPath;
        $this->projectDir = $projectDir;
    }
    public function build(array $tests, string $mutantFilePath, string $mutationHash, string $mutationOriginalFilePath) : string
    {
        $customAutoloadFilePath = sprintf('%s/interceptor.phpspec.autoload.%s.infection.php', $this->tempDirectory, $mutationHash);
        $parsedYaml = Yaml::parseFile($this->originalYamlConfigPath);
        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutationOriginalFilePath, $mutantFilePath, $parsedYaml));
        $yamlConfiguration = new MutationYamlConfiguration($this->tempDirectory, $parsedYaml, $customAutoloadFilePath);
        $newYaml = $yamlConfiguration->getYaml();
        $path = $this->buildPath($mutationHash);
        file_put_contents($path, $newYaml);
        return $path;
    }
    private function createCustomAutoloadWithInterceptor(string $originalFilePath, string $mutantFilePath, array $parsedYaml) : string
    {
        $originalBootstrap = $this->getOriginalBootstrapFilePath($parsedYaml);
        $autoloadPlaceholder = $originalBootstrap !== null ? "require_once '{$originalBootstrap}';" : '';
        $interceptorPath = IncludeInterceptor::LOCATION;
        $customAutoload = <<<AUTOLOAD
<?php

%s
%s

AUTOLOAD;
        return sprintf($customAutoload, $autoloadPlaceholder, $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutantFilePath));
    }
    private function buildPath(string $mutationHash) : string
    {
        $fileName = sprintf('phpspecConfiguration.%s.infection.yml', $mutationHash);
        return $this->tempDirectory . '/' . $fileName;
    }
    private function getOriginalBootstrapFilePath(array $parsedYaml) : ?string
    {
        if (!array_key_exists('bootstrap', $parsedYaml)) {
            return null;
        }
        return sprintf('%s/%s', $this->projectDir, $parsedYaml['bootstrap']);
    }
    private function getInterceptorFileContent(string $interceptorPath, string $originalFilePath, string $mutantFilePath) : string
    {
        $infectionPhar = '';
        if (strpos(__FILE__, 'phar:') === 0) {
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
        assert(is_string($prefix));
        return $prefix;
    }
}
