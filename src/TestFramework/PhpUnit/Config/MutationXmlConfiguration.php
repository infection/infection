<?php

declare(strict_types=1);


namespace Infection\TestFramework\PhpUnit\Config;


class MutationXmlConfiguration
{
    /**
     * @var string
     */
    private $originalXmlConfigPath;

    /**
     * @var string
     */
    private $customAutoloadFilePath;

    public function __construct(string $originalXmlConfigPath, string $customAutoloadFilePath)
    {
        $this->originalXmlConfigPath = $originalXmlConfigPath;
        $this->customAutoloadFilePath = $customAutoloadFilePath;
    }

    public function getXml() : string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
                <phpunit backupGlobals="false"
                         backupStaticAttributes="false"
                         bootstrap="' . $this->customAutoloadFilePath . '"
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
}