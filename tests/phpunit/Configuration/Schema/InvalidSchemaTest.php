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

namespace Infection\Tests\Configuration\Schema;

use Infection\Configuration\Schema\InvalidSchema;
use Infection\Configuration\Schema\SchemaConfigurationFile;
use Infection\Framework\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidSchema::class)]
final class InvalidSchemaTest extends TestCase
{
    /**
     * @param string[] $errors
     */
    #[DataProvider('configWithErrorsProvider')]
    public function test_it_can_be_instantiated(
        SchemaConfigurationFile $config,
        array $errors,
        string $expectedErrorMessage,
    ): void {
        $exception = InvalidSchema::create($config, $errors);

        $this->assertSame(
            $expectedErrorMessage,
            Str::toUnixLineEndings($exception->getMessage()),
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public static function configWithErrorsProvider(): iterable
    {
        $pathname = '/path/to/config';

        yield 'no error' => [
            new SchemaConfigurationFile($pathname),
            [],
            '"/path/to/config" does not match the expected JSON schema.',
        ];

        yield 'pseudo empty error' => [
            new SchemaConfigurationFile($pathname),
            ['', ''],
            '"/path/to/config" does not match the expected JSON schema.',
        ];

        yield 'one error' => [
            new SchemaConfigurationFile($pathname),
            ['Error message'],
            <<<'ERROR'
                "/path/to/config" does not match the expected JSON schema:
                 - Error message
                ERROR,
        ];

        yield 'multiple errors' => [
            new SchemaConfigurationFile($pathname),
            [
                'First error message',
                'Second error message',
            ],
            <<<'ERROR'
                "/path/to/config" does not match the expected JSON schema:
                 - First error message
                 - Second error message
                ERROR,
        ];

        yield 'worst case' => [
            new SchemaConfigurationFile($pathname),
            [
                ' First error message ',
                '',
                'Second error message' . "\n",
            ],
            <<<'ERROR'
                "/path/to/config" does not match the expected JSON schema:
                 - First error message
                 - Second error message
                ERROR,
        ];
    }
}
