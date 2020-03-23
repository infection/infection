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

namespace Infection\Tests\Mutator;

use function array_key_exists;
use function end;
use function explode;
use function get_class;
use Infection\CannotBeInstantiated;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function Safe\sprintf;
use function Safe\substr;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

final class MutatorFixturesProvider
{
    use CannotBeInstantiated;

    private const MUTATOR_FIXTURES_DIR = __DIR__ . '/../../autoloaded/mutator-fixtures';

    /**
     * @var array<string, string>
     */
    private static $testCaseFixtureDirMapping = [];

    public static function getFixtureFileContent(TestCase $testCase, string $file): string
    {
        Assert::isInstanceOf($testCase, BaseMutatorTestCase::class);

        return file_get_contents(sprintf(
            '%s/%s',
            self::getTestCaseFixtureDir(get_class($testCase)),
            $file
        ));
    }

    /**
     * @param class-string $testCaseClass
     */
    private static function getTestCaseFixtureDir(string $testCaseClass): string
    {
        if (array_key_exists($testCaseClass, self::$testCaseFixtureDirMapping)) {
            return self::$testCaseFixtureDirMapping[$testCaseClass];
        }

        $testCaseClassParts = explode('\\', $testCaseClass);

        return self::$testCaseFixtureDirMapping[$testCaseClass] = Path::canonicalize(sprintf(
            '%s/%s',
            self::MUTATOR_FIXTURES_DIR,
            substr(end($testCaseClassParts), 0, -4)
        ));
    }
}
