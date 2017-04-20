<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Config\ConfigBuilder;
use Infection\TestFramework\Config\TestFrameworkConfigurationFile;

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

    public function __construct(string $tempDirectory, string $originalXmlConfigPath)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalXmlConfigPath = $originalXmlConfigPath;
    }

    public function build(Mutant $mutant = null): string
    {
        $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tempDirectory,
            $mutant->getMutation()->getHash()
        );

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutant));

        $xmlConfiguration = new MutationXmlConfiguration($this->originalXmlConfigPath, $customAutoloadFilePath);
        $newXml = $xmlConfiguration->getXml();

        $path = $this->buildPath($mutant);

        file_put_contents($path, $newXml);

        return $path;
    }

    private function createCustomAutoloadWithInterceptor(Mutant $mutant) : string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();


        $autoload = '/Users/user/tmp/remove/vendor/autoload.php';

        $customAutoload = <<<AUTOLOAD
<?php

require_once '{$autoload}';

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