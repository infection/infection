<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
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
            $this->markTestSkipped('Can\'t test file permission on Windows');
        }

        $readOnlyDirPath = $this->tmpDir . '/invalid';
        $exceptionMessage = sprintf('Unable to write to the "%s" directory. Check "logs.%s" file path in infection.json.', $readOnlyDirPath, $logType);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage($exceptionMessage);

        // make it readonly
        $this->fileSystem->mkdir($readOnlyDirPath, 0400);

        if (is_writable($readOnlyDirPath)) {
            $this->markTestSkipped('Unable to change file permission to 0400');
        }

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
