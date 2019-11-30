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

namespace Infection\Tests\Json;

use const DIRECTORY_SEPARATOR;
use Generator;
use Infection\Json\Exception\ParseException;
use Infection\Json\JsonFile;
use Infection\Utils\TmpDirectoryCreator;
use JsonSchema\Exception\ValidationException;
use function microtime;
use PHPUnit\Framework\TestCase;
use function random_int;
use Symfony\Component\Filesystem\Filesystem;

final class JsonFileTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $workspace;

    /**
     * @var string
     */
    private $tmpDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->workspace = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'infection-test' . microtime(true) . random_int(100, 999);
        $this->tmpDir = (new TmpDirectoryCreator($this->filesystem))->createAndGet($this->workspace);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->workspace);
    }

    public function test_it_creates_successfully(): void
    {
        $jsonString = '{"timeout": 25, "source": {"directories": ["src"]}}';

        $jsonPath = $this->tmpDir . '/file.json';

        $this->filesystem->dumpFile($jsonPath, $jsonString);

        $content = (new JsonFile($jsonPath))->decode();

        self::assertSame(25, $content->timeout);
        self::assertSame(['src'], $content->source->directories);
    }

    public function test_it_throws_parse_exception_with_invalid_json(): void
    {
        $jsonString = '{"timeout": 25,}';

        $jsonPath = $this->tmpDir . '/file.json';

        $this->filesystem->dumpFile($jsonPath, $jsonString);

        self::expectException(ParseException::class);

        (new JsonFile($jsonPath))->decode();
    }

    public function test_it_throws_parse_exception_when_file_is_not_found(): void
    {
        $jsonPath = $this->tmpDir . '/missing-invalid.json';
        self::assertFileNotExists($jsonPath);

        self::expectException(ParseException::class);

        (new JsonFile($jsonPath))->decode();
    }

    public function test_it_throws_schema_validation_exception(): void
    {
        $jsonString = '{"timeout": 25}';

        $jsonPath = $this->tmpDir . '/file.json';

        $this->filesystem->dumpFile($jsonPath, $jsonString);

        self::expectException(ValidationException::class);

        (new JsonFile($jsonPath))->decode();
    }

    /**
     * @dataProvider validTrueValueProvider
     */
    public function test_it_validates_true_value_mutator(string $jsonString): void
    {
        $jsonPath = $this->tmpDir . '/file.json';

        $this->filesystem->dumpFile($jsonPath, $jsonString);

        $content = (new JsonFile($jsonPath))->decode();

        self::assertObjectHasAttribute('mutators', $content);
    }

    public function validTrueValueProvider(): Generator
    {
        yield 'Boolean value' => [
            <<<JSON
{
    "timeout": 25,
    "source": {"directories": ["src"]},
    "mutators": {
        "TrueValue": true
    }
}
JSON
        ];

        yield 'Object value' => [
            <<<JSON
{
    "timeout": 25,
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "TrueValue": {
            "ignore": [
                "IgnoreClass"
            ],
            "settings": {
                "in_array": false,
                "array_search": true
            }
        }
    }
}
JSON
        ];
    }

    /**
     * @dataProvider invalidTrueValueProvider
     */
    public function test_it_throws_exception_for_invalid_true_value_mutator(string $jsonString, string $expectedMessageRegex): void
    {
        $jsonPath = $this->tmpDir . '/file.json';

        $this->filesystem->dumpFile($jsonPath, $jsonString);

        self::expectException(ValidationException::class);
        self::expectExceptionMessageRegExp($expectedMessageRegex);

        (new JsonFile($jsonPath))->decode();
    }

    public function invalidTrueValueProvider(): Generator
    {
        yield 'Extra property for TrueValue mutator' => [
            <<<'JSON'
{
    "timeout": 25,
    "source": {"directories": ["src"]},
    "mutators": {
        "TrueValue": {
            "EXTRA_KEY": true,
            "ignore": [
                "IgnoreClass"
            ],
            "settings": {
                "in_array": false,
                "array_search": true
            }
        }
    }
}
JSON
            ,
            '/mutators\.TrueValue : The property EXTRA_KEY is not defined and the definition does not allow additional properties/',
        ];

        yield 'Extra property for TrueValue mutator, settings object' => [
            <<<'JSON'
{
    "timeout": 25,
    "source": {"directories": ["src"]},
    "mutators": {
        "TrueValue": {
            "ignore": [
                "IgnoreClass"
            ],
            "settings": {
                "EXTRA_KEY": true,
                "in_array": false,
                "array_search": true
            }
        }
    }
}
JSON
            ,
            '/mutators\.TrueValue\.settings : The property EXTRA_KEY is not defined and the definition does not allow additional properties/',
        ];

        yield 'Invalid type for "in_array" setting' => [
            <<<'JSON'
{
    "timeout": 25,
    "source": {"directories": ["src"]},
    "mutators": {
        "TrueValue": {
            "ignore": [
                "IgnoreClass"
            ],
            "settings": {
                "in_array": 123,
                "array_search": true
            }
        }
    }
}
JSON
            ,
            '/mutators\.TrueValue\.settings\.in_array : Integer value found, but a boolean is required/',
        ];

        yield 'Invalid type TrueValue' => [
            <<<'JSON'
{
    "timeout": 25,
    "source": {"directories": ["src"]},
    "mutators": {
        "TrueValue": 123
    }
}
JSON
            ,
            '/mutators\.TrueValue : Failed to match at least one schema/',
        ];
    }
}
