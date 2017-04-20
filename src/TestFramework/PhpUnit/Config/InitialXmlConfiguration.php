<?php

declare(strict_types=1);


namespace Infection\TestFramework\PhpUnit\Config;


class InitialXmlConfiguration
{
    /**
     * @var string
     */
    private $tempDirectory;

    public function __construct(string $tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }

    public function getXml() : string
    {
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