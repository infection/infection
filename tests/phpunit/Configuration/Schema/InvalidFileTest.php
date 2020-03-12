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

use Error;
use Infection\Configuration\Schema\InvalidFile;
use Infection\Configuration\Schema\SchemaConfigurationFile;
use PHPUnit\Framework\TestCase;
use Throwable;

final class InvalidFileTest extends TestCase
{
    public function test_it_can_be_created_for_file_not_found(): void
    {
        $config = new SchemaConfigurationFile('/path/to/config');

        $exception = InvalidFile::createForFileNotFound($config);

        $this->assertSame(
            'The file "/path/to/config" could not be found or is not a file.',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_it_can_be_created_for_file_not_readable(): void
    {
        $config = new SchemaConfigurationFile('/path/to/config');

        $exception = InvalidFile::createForFileNotReadable($config);

        $this->assertSame(
            'The file "/path/to/config" is not readable.',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_it_can_be_created_for_file_content_could_not_be_retrieved(): void
    {
        $config = new SchemaConfigurationFile('/path/to/config');

        $exception = InvalidFile::createForCouldNotRetrieveFileContents($config);

        $this->assertSame(
            'Could not retrieve the contents of the file "/path/to/config".',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * @dataProvider jsonErrorProvider
     */
    public function test_it_can_be_created_for_file_with_invalid_JSON_content(
        SchemaConfigurationFile $config,
        string $error,
        Throwable $previous,
        string $expectedErrorMessage
    ): void {
        $exception = InvalidFile::createForInvalidJson($config, $error, $previous);

        $this->assertSame($expectedErrorMessage, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function jsonErrorProvider(): iterable
    {
        yield [
            new SchemaConfigurationFile('/path/to/config'),
            'Error message',
            new Error(),
            'Could not parse the JSON file "/path/to/config": Error message',
        ];

        yield 'empty error message' => [
            new SchemaConfigurationFile('/path/to/config'),
            '',
            new Error(),
            'Could not parse the JSON file "/path/to/config": ',
        ];
    }
}
