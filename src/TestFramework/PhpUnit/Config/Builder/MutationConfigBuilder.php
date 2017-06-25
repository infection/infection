<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Builder;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpUnit\Config\MutationXmlConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class MutationConfigBuilder implements ConfigBuilder
{
    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * @var string
     */
    private $originalXmlConfigPath;
    /**
     * @var PathReplacer
     */
    private $pathReplacer;
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(string $tempDirectory, string $originalXmlConfigPath, PathReplacer $pathReplacer, string $projectDir)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalXmlConfigPath = $originalXmlConfigPath;
        $this->pathReplacer = $pathReplacer;
        $this->projectDir = $projectDir;
    }

    public function build(Mutant $mutant): string
    {
        $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tempDirectory,
            $mutant->getMutation()->getHash()
        );

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutant));

        $xmlConfiguration = new MutationXmlConfiguration(
            $this->tempDirectory,
            $this->originalXmlConfigPath,
            $this->pathReplacer,
            $customAutoloadFilePath,
            $mutant->getCoverageTests()
        );

        $newXml = $xmlConfiguration->getXml();

        $path = $this->buildPath($mutant);

        file_put_contents($path, $newXml);

        return $path;
    }

    private function createCustomAutoloadWithInterceptor(Mutant $mutant) : string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();
        $interceptorPath = dirname(__DIR__, 4) .  '/StreamWrapper/IncludeInterceptor.php';

        // TODO change to what it was (e.g. app/autoload - see simplehabits)
        $autoload = sprintf('%s/vendor/autoload.php', $this->projectDir);

        $customAutoload = <<<AUTOLOAD
<?php

require_once '{$autoload}';
require_once '{$interceptorPath}';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('{$originalFilePath}', '{$mutatedFilePath}');
IncludeInterceptor::enable();

AUTOLOAD;

        return $customAutoload;
    }

    private function buildPath(Mutant $mutant): string
    {
        $fileName = sprintf('phpunitConfiguration.%s.infection.xml', $mutant->getMutation()->getHash());

        return $this->tempDirectory . '/' . $fileName;
    }
}