<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\Tests\Json\Exception;

use Infection\Json\Exception\JsonValidationException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class JsonValidationExceptionTest extends TestCase
{
    public function test_does_not_match_schema_with_errors(): void
    {
        $path = '/tmp/config.json';
        $errors = [
            'mutators : Must contain a minimum of 1 properties',
        ];

        $exception = JsonValidationException::doesNotMatchSchema($path, $errors);

        $this->assertInstanceOf(JsonValidationException::class, $exception);

        $expected = '"' . $path . '" does not match the expected JSON schema:' . PHP_EOL . ' - ' . implode(PHP_EOL . ' - ', $errors);

        $this->assertSame($expected, $exception->getMessage());
    }

    public function test_does_not_match_schema_without_errors(): void
    {
        $path = '/tmp/config.json';

        $exception = JsonValidationException::doesNotMatchSchema($path);

        $this->assertInstanceOf(JsonValidationException::class, $exception);

        $expected = '"' . $path . '" does not match the expected JSON schema.';

        $this->assertSame($expected, $exception->getMessage());
    }
}
