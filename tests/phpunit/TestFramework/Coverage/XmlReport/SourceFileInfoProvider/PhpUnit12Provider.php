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

namespace Infection\Tests\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;

use function dirname;
use Infection\CannotBeInstantiated;
use Symfony\Component\Filesystem\Path;

final class PhpUnit12Provider
{
    use CannotBeInstantiated;

    private const FIXTURES_DIR = __DIR__ . '/../../Fixtures';

    public static function infoProvider(): iterable
    {
        $phpunit120IndexPath = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/index.xml');

        $createPhpUnit120Scenario = static fn (
            string $relativeCoverageFilePath,
            string $expected,
        ) => [
            $phpunit120IndexPath,
            dirname($phpunit120IndexPath),
            $relativeCoverageFilePath,
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src',
            $expected,
        ];

        yield 'covered class' => $createPhpUnit120Scenario(
            'Covered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/Calculator.php',
        );

        yield 'covered trait' => $createPhpUnit120Scenario(
            'Covered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/LoggerTrait.php',
        );

        yield 'covered class with trait' => $createPhpUnit120Scenario(
            'Covered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/UserService.php',
        );

        yield 'covered function' => $createPhpUnit120Scenario(
            'Covered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/functions.php',
        );

        yield 'uncovered class' => $createPhpUnit120Scenario(
            'Uncovered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/Calculator.php',
        );

        yield 'uncovered trait' => $createPhpUnit120Scenario(
            'Uncovered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/LoggerTrait.php',
        );

        yield 'uncovered class with trait' => $createPhpUnit120Scenario(
            'Uncovered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/UserService.php',
        );

        yield 'uncovered function' => $createPhpUnit120Scenario(
            'Uncovered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/functions.php',
        );
    }
}
