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

namespace Infection\Tests\TestFramework\Factory;

use Infection\CannotBeInstantiated;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\TestFrameworkFactory;
use Webmozart\Assert\Assert;

final class ConfigurableTestFrameworkFactory implements TestFrameworkFactory
{
    use CannotBeInstantiated;

    private const string NOT_CONFIGURED_MESSAGE = 'TestFrameworkFactory is not configured. Call configure() before using it';

    private static bool $configured = false;

    private static ?TestFramework $testFramework = null;

    private static ?string $name = null;

    private static ?string $executableName = null;

    public static function configure(
        TestFramework $testFramework,
        string $name,
        string $executableName,
    ): void {
        self::assertNotConfigured();
        self::$configured = true;

        self::$testFramework = $testFramework;
        self::$name = $name;
        self::$executableName = $executableName;
    }

    public static function reset(): void
    {
        self::$configured = false;

        self::$testFramework = null;
        self::$name = null;
        self::$executableName = null;
    }

    /**
     * @param array<array-key, mixed> $sourceDirectories
     */
    public static function create(
        string $testFrameworkExecutable,
        string $tmpDir,
        string $testFrameworkConfigPath,
        ?string $testFrameworkConfigDir,
        string $jUnitFilePath,
        string $projectDir,
        array $sourceDirectories,
        bool $skipCoverage,
    ): TestFramework {
        $testFramework = self::$testFramework;
        Assert::notNull($testFramework, self::NOT_CONFIGURED_MESSAGE);

        return $testFramework;
    }

    public static function getAdapterName(): string
    {
        $name = self::$name;
        Assert::notNull($name, self::NOT_CONFIGURED_MESSAGE);

        return $name;
    }

    public static function getExecutableName(): string
    {
        $executableName = self::$executableName;
        Assert::notNull($executableName, self::NOT_CONFIGURED_MESSAGE);

        return $executableName;
    }

    private static function assertNotConfigured(): void
    {
        Assert::false(
            self::$configured,
            'TestFrameworkFactory is already configured',
        );
    }
}
