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

use ColinODell\Json5\SyntaxError;
use Exception;
use Infection\Configuration\Schema\InvalidFile;
use Infection\Configuration\Schema\SchemaConfigurationFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function sprintf;

#[Group('integration')]
#[CoversClass(SchemaConfigurationFile::class)]
final class SchemaConfigurationFileTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../Fixtures/Configuration';

    public function test_it_can_be_instantiated(): void
    {
        $pathname = '/nowhere';

        $config = new SchemaConfigurationFile($pathname);

        $this->assertSame($pathname, $config->getPathname());
    }

    public function test_its_contents_is_retrieved_lazily(): void
    {
        $invalidPathname = '/nowhere';

        $config = new SchemaConfigurationFile($invalidPathname);

        try {
            $config->getDecodedContents();

            $this->fail('Expected the content to be invalid.');
        } catch (Exception) {
            $this->addToAssertionCount(1);
        }

        $validPathname = self::FIXTURES_DIR . '/file.json';
        $expectedArrayContents = ['foo' => 'bar'];

        $config = new SchemaConfigurationFile($validPathname);
        $actualContents = $config->getDecodedContents();

        $this->assertSame($expectedArrayContents, (array) $actualContents);
    }

    public function test_its_contents_is_retrieved_only_once(): void
    {
        $config = new SchemaConfigurationFile(self::FIXTURES_DIR . '/file.json');
        $expectedValue = (object) ['a' => 'b'];

        // Fetch the contents once
        $config->getDecodedContents();

        $decodedContentsReflection = (new ReflectionClass(
            SchemaConfigurationFile::class))->getProperty('decodedContents');
        $decodedContentsReflection->setValue($config, $expectedValue);

        $this->assertSame($expectedValue, $config->getDecodedContents());
    }

    /**
     * @param non-empty-string $pathname
     */
    #[DataProvider('invalidConfigContentsProvider')]
    public function test_it_cannot_retrieve_or_decode_invalid_contents(
        string $pathname,
        Exception $expectedException,
    ): void {
        $config = new SchemaConfigurationFile($pathname);

        try {
            $config->getDecodedContents();

            $this->fail('Expected the config contents to be invalid.');
        } catch (Exception $exception) {
            $this->assertSame(
                $expectedException->getMessage(),
                $exception->getMessage(),
            );
            $this->assertSame(
                $expectedException->getCode(),
                $exception->getCode(),
            );

            if ($expectedException->getPrevious() === null) {
                $this->assertNull($exception->getPrevious());
            } else {
                $expectedPrevious = $expectedException->getPrevious();
                $previous = $exception->getPrevious();

                $this->assertNotNull($previous);
                $this->assertInstanceOf($expectedPrevious::class, $previous);
                $this->assertSame($expectedPrevious->getMessage(), $previous->getMessage());
                $this->assertSame($expectedPrevious->getCode(), $previous->getCode());
                $this->assertSame($expectedPrevious->getPrevious(), $previous->getPrevious());
            }
        }
    }

    public static function invalidConfigContentsProvider(): iterable
    {
        yield 'unknown path' => [
            '/nowhere',
            new InvalidFile('The file "/nowhere" could not be found or is not a file.'),
        ];

        yield 'file is a directory' => [
            self::FIXTURES_DIR,
            new InvalidFile(sprintf(
                'The file "%s" could not be found or is not a file.',
                self::FIXTURES_DIR,
            )),
        ];

        yield 'invalid JSON contents' => [
            self::FIXTURES_DIR . '/invalid-json',
            new InvalidFile(
                sprintf(
                    'Could not parse the JSON file "%s": Unexpected EOF at line 1 column 1 of the JSON5 data',
                    self::FIXTURES_DIR . '/invalid-json',
                ),
                0,
                new SyntaxError('Unexpected EOF', 1, 1),
            ),
        ];
    }
}
