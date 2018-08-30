<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config;

use Infection\Config\InfectionConfig;
use Infection\Config\Validator;
use Infection\Logger\ResultsLoggerTypes;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class ValidatorTest extends TestCase
{
    /**
     * @var TmpDirectoryCreator
     */
    private $creator;

    /**
     * @var string
     */
    private $workspace;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $tmpDir;

    protected function setUp(): void
    {
        $this->fileSystem = new Filesystem();
        $this->creator = new TmpDirectoryCreator($this->fileSystem);
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);
        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->workspace);
    }

    /**
     * @dataProvider invalidFilePaths
     */
    public function test_it_validates_log_file_paths(string $logType): void
    {
        if (\DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Can\' test file permission on Windows');
        }

        $readOnlyDirPath = $this->tmpDir . '/invalid';
        $exceptionMessage = sprintf('Unable to write to the "%s" directory. Check "logs.%s" file path in infection.json.', $readOnlyDirPath, $logType);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage($exceptionMessage);

        // make it readonly
        $this->fileSystem->mkdir($readOnlyDirPath, 0400);

        $configObject = json_decode(sprintf('{"logs": {"%s": "%s/infection.log"}}', $logType, $readOnlyDirPath));

        $config = new InfectionConfig($configObject, $this->fileSystem, '');
        $validator = new Validator();

        $validator->validate($config);
    }

    public function invalidFilePaths(): \Generator
    {
        $logTypes = array_diff(ResultsLoggerTypes::ALL, [ResultsLoggerTypes::BADGE]);

        foreach ($logTypes as $logType) {
            yield "Throws exception when {$logType} logger has invalid path" => [$logType];
        }
    }
}
