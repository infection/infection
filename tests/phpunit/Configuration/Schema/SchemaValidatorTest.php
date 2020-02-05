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

use Generator;
use Infection\Configuration\Schema\InvalidSchema;
use Infection\Configuration\Schema\SchemaConfigurationFile;
use Infection\Configuration\Schema\SchemaValidator;
use function Infection\Tests\normalizeLineReturn;
use function json_decode;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\json_last_error_msg;
use Webmozart\Assert\Assert;

final class SchemaValidatorTest extends TestCase
{
    /**
     * @dataProvider configProvider
     */
    public function test_it_validates_the_given_raw_config(
        SchemaConfigurationFile $config,
        ?string $expectedErrorMessage
    ): void {
        try {
            (new SchemaValidator())->validate($config);

            if ($expectedErrorMessage !== null) {
                $this->fail('Expected the config to be invalid.');
            } else {
                $this->addToAssertionCount(1);
            }
        } catch (InvalidSchema $exception) {
            if ($expectedErrorMessage === null) {
                $this->fail('Did not expect the config to be invalid.');
            } else {
                $this->assertSame(
                    $expectedErrorMessage,
                    normalizeLineReturn($exception->getMessage())
                );
            }
        }
    }

    public function configProvider(): Generator
    {
        $path = '/path/to/config';

        yield 'empty JSON' => [
            self::createConfigWithContents(
                $path,
               '{}'
            ),
            <<<'ERROR'
"/path/to/config" does not match the expected JSON schema:
 - [source] The property source is required
ERROR
            ,
        ];

        yield 'invalid timeout' => [
            self::createConfigWithContents(
                $path,
                '{"timeout": "10"}'
            ),
            <<<'ERROR'
"/path/to/config" does not match the expected JSON schema:
 - [source] The property source is required
 - [timeout] String value found, but an integer is required
ERROR
            ,
        ];

        yield 'valid schema' => [
            self::createConfigWithContents(
                $path,
                <<<'JSON'
{
    "source": {
        "directories": ["src"]
    }
}
JSON
            ),
            null,
        ];
    }

    private static function createConfigWithContents(
        string $path,
        string $contents
    ): SchemaConfigurationFile {
        $config = new SchemaConfigurationFile($path);

        $decodedContents = json_decode($contents);

        Assert::notNull($decodedContents, json_last_error_msg());

        $configReflection = new ReflectionClass(SchemaConfigurationFile::class);

        $decodedContentsReflection = $configReflection->getProperty('decodedContents');
        $decodedContentsReflection->setAccessible(true);
        $decodedContentsReflection->setValue($config, $decodedContents);

        return $config;
    }
}
