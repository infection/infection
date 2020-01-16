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

namespace Infection\Tests\Logger;

use Generator;
use Infection\Logger\TextFileLogger;
use Infection\Mutant\MetricsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group integration Requires some I/O operations
 *
 * @covers \Infection\Logger\FileLogger
 * @covers \Infection\Logger\TextFileLogger
 */
final class TextFileLoggerTest extends TestCase
{
    use CreateMetricsCalculator;

    private const LOG_FILE_PATH = '/path/to/text.log';

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    protected function setUp(): void
    {
        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->outputMock = $this->createMock(OutputInterface::class);
    }

    /**
     * @dataProvider emptyMetricsProvider
     */
    public function test_it_logs_results_in_a_text_file_when_there_is_no_mutation(
        bool $debugVerbosity,
        bool $debugMode,
        string $expectedContent
    ): void {
        $this->fileSystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with(self::LOG_FILE_PATH, $expectedContent)
        ;

        $logger = new TextFileLogger(
            $this->outputMock,
            self::LOG_FILE_PATH,
            new MetricsCalculator(),
            $this->fileSystemMock,
            $debugVerbosity,
            $debugMode
        );

        $logger->log();
    }

    /**
     * @dataProvider completeMetricsProvider
     */
    public function test_it_logs_results_in_a_text_file_when_there_are_mutations(
        bool $debugVerbosity,
        bool $debugMode,
        string $expectedContent
    ): void {
        $this->fileSystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with(self::LOG_FILE_PATH, $expectedContent)
        ;

        $logger = new TextFileLogger(
            $this->outputMock,
            self::LOG_FILE_PATH,
            $this->createCompleteMetricsCalculator(),
            $this->fileSystemMock,
            $debugVerbosity,
            $debugMode
        );

        $logger->log();
    }

    public function test_it_cannot_log_on_invalid_streams(): void
    {
        $this->outputMock
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>The only streams supported are php://stdout and php://stderr</error>')
        ;

        $debugFileLogger = new TextFileLogger(
            $this->outputMock,
            'php://memory',
            new MetricsCalculator(),
            $this->fileSystemMock,
            false,
            false
        );

        $debugFileLogger->log();
    }

    public function test_it_fails_if_cannot_write_file(): void
    {
        $this->fileSystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with(self::LOG_FILE_PATH, $this->anything())
            ->willThrowException(new IOException('Cannot write in directory X'));

        $this->outputMock
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>Cannot write in directory X</error>')
        ;

        $debugFileLogger = new TextFileLogger(
            $this->outputMock,
            self::LOG_FILE_PATH,
            new MetricsCalculator(),
            $this->fileSystemMock,
            false,
            false
        );

        $debugFileLogger->log();
    }

    public function emptyMetricsProvider(): Generator
    {
        yield 'no debug verbosity; no debug mode' => [
            false,
            false,
            <<<'TXT'
Escaped mutants:
================

Timed Out mutants:
==================

Not Covered mutants:
====================

TXT
        ];

        yield 'debug verbosity; no debug mode' => [
            true,
            false,
            <<<'TXT'
Escaped mutants:
================

Timed Out mutants:
==================

Killed mutants:
===============

Errors mutants:
===============

Not Covered mutants:
====================

TXT
        ];

        yield 'no debug verbosity; debug mode' => [
            false,
            true,
            <<<'TXT'
Escaped mutants:
================

Timed Out mutants:
==================

Not Covered mutants:
====================

TXT
        ];

        yield 'debug verbosity; debug mode' => [
            true,
            true,
            <<<'TXT'
Escaped mutants:
================

Timed Out mutants:
==================

Killed mutants:
===============

Errors mutants:
===============

Not Covered mutants:
====================

TXT
        ];
    }

    public function completeMetricsProvider(): Generator
    {
        yield 'no debug verbosity; no debug mode' => [
            false,
            false,
            <<<'TXT'
Escaped mutants:
================


1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#1';


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';

Timed Out mutants:
==================


1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';

Not Covered mutants:
====================


1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#1';


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

TXT
        ];

        yield 'debug verbosity; no debug mode' => [
            true,
            false,
            <<<'TXT'
Escaped mutants:
================


1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#1';



2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';


Timed Out mutants:
==================


1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';



2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';


Killed mutants:
===============


1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#1';



2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#0';


Errors mutants:
===============


1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#1';



2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#0';


Not Covered mutants:
====================


1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#1';



2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';


TXT
        ];

        yield 'no debug verbosity; debug mode' => [
            false,
            true,
            <<<'TXT'
Escaped mutants:
================


1) foo/bar:9    [M] PregQuote
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#1';


2) foo/bar:10    [M] For_
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';

Timed Out mutants:
==================


1) foo/bar:9    [M] PregQuote
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';


2) foo/bar:10    [M] For_
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';

Not Covered mutants:
====================


1) foo/bar:9    [M] PregQuote
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#1';


2) foo/bar:10    [M] For_
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

TXT
        ];

        yield 'debug verbosity; debug mode' => [
            true,
            true,
            <<<'TXT'
Escaped mutants:
================


1) foo/bar:9    [M] PregQuote
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#1';



2) foo/bar:10    [M] For_
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';


Timed Out mutants:
==================


1) foo/bar:9    [M] PregQuote
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';



2) foo/bar:10    [M] For_
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';


Killed mutants:
===============


1) foo/bar:9    [M] PregQuote
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#1';



2) foo/bar:10    [M] For_
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#0';


Errors mutants:
===============


1) foo/bar:9    [M] PregQuote
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#1';



2) foo/bar:10    [M] For_
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#0';


Not Covered mutants:
====================


1) foo/bar:9    [M] PregQuote
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#1';



2) foo/bar:10    [M] For_
bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';


TXT
        ];
    }
}
