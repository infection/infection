<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\TestFramework\Config\ConfigBuilder as ConfigBuilderInterface;


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

    public function build()
    {
        file_put_contents($this->getPath(), '<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false"
         backupStaticAttributes="false"
         bootstrap="./vendor/autoload.php"
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
            <directory>./tests/</directory>
        </testsuite>
    </testsuites>

    <filter>
        <whitelist>
            <directory>./src/</directory>
        </whitelist>
    </filter>
    <logging>
      <log type="coverage-php" target="/tmp/coverage.serialized"/>
    </logging>
</phpunit>');
    }

    public function getPath() : string
    {
        return $this->tempDirectory . '/initial.configuration.xml';
    }
}