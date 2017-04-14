<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Config\ConfigBuilder as ConfigBuilderInterface;
use Infection\TestFramework\Config\TestFrameworkConfigurationFile;


class ConfigBuilder implements ConfigBuilderInterface
{
    /**
     * @var string
     */
    private $tempDirectory;

    public function __construct(string $tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }

    public function build(Mutant $mutant = null) : TestFrameworkConfigurationFile
    {
        $path = $this->buildPath($mutant);

        file_put_contents($path, $this->getXml($mutant));

        return new TestFrameworkConfigurationFile($path);
    }

    private function buildPath(Mutant $mutant = null) : string
    {
        $fileName = 'phpunitConfiguration.initial.infection.xml';

        if ($mutant) {
            $fileName = sprintf('phpunitConfiguration.%s.infection.xml', $mutant->getMutation()->getHash());
        }

        return $this->tempDirectory . '/' . $fileName;
    }

    private function getXml(Mutant $mutant = null) : string
    {
        if ($mutant) {
            $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
            $mutatedFilePath = $mutant->getMutatedFilePath();

            $customAutoloadFilePath = sprintf(
                '%s/interceptor.autoload.%s.infection.php',
                $this->tempDirectory,
                $mutant->getMutation()->getHash()
            );
            $autoload = '/Users/user/tmp/remove/vendor/autoload.php';

            $customAutoload = <<<AUTOLOAD
<?php

require_once '{$autoload}';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('{$originalFilePath}', '{$mutatedFilePath}');
IncludeInterceptor::enable();

AUTOLOAD;

            file_put_contents($customAutoloadFilePath, $customAutoload);

            return '<?xml version="1.0" encoding="UTF-8"?>
                <phpunit backupGlobals="false"
                         backupStaticAttributes="false"
                         bootstrap="' . $customAutoloadFilePath . '"
                         colors="true"
                         convertErrorsToExceptions="true"
                         convertNoticesToExceptions="true"
                         convertWarningsToExceptions="true"
                         processIsolation="false"
                         stopOnFailure="false"
                         syntaxCheck="false"
                >
                    <testsuites>
                        <testsuite name="Application Test Suite">
                            <directory>/Users/user/tmp/remove/tests/</directory>
                        </testsuite>
                    </testsuites>
                
                    <filter>
                        <whitelist>
                            <directory>/Users/user/tmp/remove/src/</directory>
                        </whitelist>
                    </filter>
                </phpunit>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>
                <phpunit backupGlobals="false"
                         backupStaticAttributes="false"
                         bootstrap="/Users/user/tmp/remove/vendor/autoload.php"
                         colors="true"
                         convertErrorsToExceptions="true"
                         convertNoticesToExceptions="true"
                         convertWarningsToExceptions="true"
                         processIsolation="false"
                         stopOnFailure="false"
                         syntaxCheck="false"
                >
                    <testsuites>
                        <testsuite name="Application Test Suite">
                            <directory>/Users/user/tmp/remove/tests/</directory>
                        </testsuite>
                    </testsuites>
                
                    <filter>
                        <whitelist>
                            <directory>/Users/user/tmp/remove/src/</directory>
                        </whitelist>
                    </filter>
                    <logging>
                      <log type="coverage-php" target="' . ($this->tempDirectory  . '/coverage.infection.php') . '"/>
                    </logging>
                </phpunit>';
    }
}