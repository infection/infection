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

namespace Infection\Tests\TestFramework;

use Infection\TestFramework\TestFrameworkTypes;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkFactory;
use PHPUnit\Framework\TestCase;

final class TestFrameworkTypesTest extends TestCase
{
    public function test_it_returns_default_types_when_no_test_framework_adapters_are_installed(): void
    {
        $types = TestFrameworkTypes::getTypes([]);

        $this->assertSame(
            [
                TestFrameworkTypes::PEST,
                TestFrameworkTypes::PHPUNIT,
                TestFrameworkTypes::PHPSPEC,
                TestFrameworkTypes::CODECEPTION,
            ],
            $types
        );
    }

    public function test_it_uses_installed_test_framework_adapters(): void
    {
        $types = TestFrameworkTypes::getTypes(
            [
                'infection/codeception-adapter' => [
                    'install_path' => '/path/to/dummy/adapter/factory.php',
                    'extra' => ['class' => DummyTestFrameworkFactory::class],
                    'version' => '1.0.0',
                ],
            ]
        );

        $this->assertSame(
            [
                TestFrameworkTypes::PEST,
                TestFrameworkTypes::PHPUNIT,
                TestFrameworkTypes::PHPSPEC,
                TestFrameworkTypes::CODECEPTION,
                'dummy',
            ],
            $types
        );
    }
}
