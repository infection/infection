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

use Infection\Logger\TextFileLogger;
use Infection\Metrics\MetricsCalculator;
use PHPUnit\Framework\TestCase;

final class TextFileLoggerTest extends TestCase
{
    use CreateMetricsCalculator;
    use LineLoggerAssertions;

    /**
     * @dataProvider emptyMetricsProvider
     */
    public function test_it_logs_results_in_a_text_file_when_there_is_no_mutation(
        bool $debugVerbosity,
        bool $onlyCoveredMode,
        bool $debugMode,
        string $expectedContents
    ): void {
        $logger = new TextFileLogger(
            new MetricsCalculator(),
            $debugVerbosity,
            $onlyCoveredMode,
            $debugMode
        );

        $this->assertLoggedContentIs($expectedContents, $logger);
    }

    /**
     * @dataProvider completeMetricsProvider
     */
    public function test_it_logs_results_in_a_text_file_when_there_are_mutations(
        bool $debugVerbosity,
        bool $onlyCoveredMode,
        bool $debugMode,
        string $expectedContents
    ): void {
        $logger = new TextFileLogger(
            $this->createCompleteMetricsCalculator(),
            $debugVerbosity,
            $onlyCoveredMode,
            $debugMode
        );

        $this->assertLoggedContentIs($expectedContents, $logger);
    }

    public function emptyMetricsProvider(): iterable
    {
        yield 'no debug verbosity; no debug mode' => [
            false,
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
            false,
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

        yield 'no debug verbosity; no debug mode; only covered' => [
            false,
            true,
            false,
            <<<'TXT'
Escaped mutants:
================

Timed Out mutants:
==================

TXT
        ];

        yield 'debug verbosity; no debug mode; only covered' => [
            true,
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

TXT
        ];

        yield 'no debug verbosity; debug mode; only covered' => [
            false,
            true,
            true,
            <<<'TXT'
Escaped mutants:
================

Timed Out mutants:
==================

TXT
        ];

        yield 'debug verbosity; debug mode; only covered' => [
            true,
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

TXT
        ];
    }

    public function completeMetricsProvider(): iterable
    {
        yield 'no debug verbosity; no debug mode' => [
            false,
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

  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';

  process output


Timed Out mutants:
==================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';

  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';

  process output


Killed mutants:
===============

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#1';

  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#0';

  process output


Errors mutants:
===============

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#1';

  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#0';

  process output


Not Covered mutants:
====================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#1';

  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

  process output

TXT
        ];

        yield 'no debug verbosity; debug mode' => [
            false,
            false,
            true,
            <<<'TXT'
Escaped mutants:
================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


Timed Out mutants:
==================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


Not Covered mutants:
====================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"

TXT
        ];

        yield 'debug verbosity; debug mode' => [
            true,
            false,
            true,
            <<<'TXT'
Escaped mutants:
================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


Timed Out mutants:
==================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


Killed mutants:
===============

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


Errors mutants:
===============

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


Not Covered mutants:
====================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output

TXT
        ];

        yield 'no debug verbosity; no debug mode; only covered' => [
            false,
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

TXT
        ];

        yield 'debug verbosity; no debug mode; only covered' => [
            true,
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

  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';

  process output


Timed Out mutants:
==================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';

  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';

  process output


Killed mutants:
===============

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#1';

  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#0';

  process output


Errors mutants:
===============

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#1';

  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#0';

  process output

TXT
        ];

        yield 'no debug verbosity; debug mode; only covered' => [
            false,
            true,
            true,
            <<<'TXT'
Escaped mutants:
================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


Timed Out mutants:
==================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"

TXT
        ];

        yield 'debug verbosity; debug mode; only covered' => [
            true,
            true,
            true,
            <<<'TXT'
Escaped mutants:
================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


Timed Out mutants:
==================

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


Killed mutants:
===============

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


Errors mutants:
===============

1) foo/bar:9    [M] PregQuote

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#1';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output


2) foo/bar:10    [M] For_

--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'error#0';

$ bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"
  process output

TXT
        ];
    }
}
