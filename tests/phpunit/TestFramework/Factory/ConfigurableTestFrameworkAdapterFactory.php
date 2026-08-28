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

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use Infection\CannotBeInstantiated;
use Webmozart\Assert\Assert;

final class ConfigurableTestFrameworkAdapterFactory implements TestFrameworkAdapterFactory
{
    use CannotBeInstantiated;

    private static bool $configured = false;

    private static ?TestFrameworkAdapter $adapter = null;

    private static ?string $name = null;

    private static ?string $executableName = null;

    public static function configure(
        TestFrameworkAdapter $adapter,
        string $name,
        string $executableName,
    ): void {
        self::assertNotConfigured();
        self::$configured = true;

        self::$adapter = $adapter;
        self::$name = $name;
        self::$executableName = $executableName;
    }

    public static function reset(): void
    {
        self::$configured = false;

        self::$adapter = null;
        self::$name = null;
        self::$executableName = null;
    }

    public static function create(
        string $testFrameworkExecutable,
        string $tmpDir,
        string $testFrameworkConfigPath,
        ?string $testFrameworkConfigDir,
        string $jUnitFilePath,
        string $projectDir,
        array $sourceDirectories,
        bool $skipCoverage,
    ): TestFrameworkAdapter {
        self::assertConfigured();

        return self::$adapter;
    }

    public static function getAdapterName(): string
    {
        self::assertConfigured();

        return self::$name;
    }

    public static function getExecutableName(): string
    {
        self::assertConfigured();

        return self::$executableName;
    }

    private static function assertNotConfigured(): void
    {
        Assert::false(
            self::$configured,
            'TestFrameworkAdapterFactory is already configured',
        );
    }

    private static function assertConfigured(): void
    {
        Assert::true(
            self::$configured,
            'TestFrameworkAdapterFactory is not configured. Call configure() before using it',
        );
    }
}
