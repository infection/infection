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

namespace Infection\Tests\AutoReview\IntegrationGroup;

use Infection\Tests\AutoReview\PhpDoc\PHPDocParser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\array_flip;
use function Safe\sprintf;
use function strpos;

final class IntegrationGroupTest extends TestCase
{
    /**
     * @var PHPDocParser|null
     */
    private static $phpDocParser;

    public static function setUpBeforeClass(): void
    {
        self::$phpDocParser = new PHPDocParser();
    }

    public static function tearDownAfterClass(): void
    {
        self::$phpDocParser = null;
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\IntegrationGroup\IntegrationGroupProvider::ioTestCaseTupleProvider
     */
    public function test_the_test_cases_requiring_IO_operations_belongs_to_the_integration_group(
        string $testCaseClassName,
        string $fileWithIoOperations
    ): void {
        $reflectionClass = new ReflectionClass($testCaseClassName);

        $phpDoc = (string) $reflectionClass->getDocComment();

        $this->assertArrayHasKey(
            '@group',
            array_flip(self::$phpDocParser->parse($phpDoc)),
            sprintf(
                <<<'TXT'
Expected the test case "%s" to have the annotation `@group integration` as I/O operations have been
found in the file "%s".
TXT
                ,
                $testCaseClassName,
                $fileWithIoOperations
            )
        );

        if (strpos($phpDoc, '@group integration') === false
            && strpos($phpDoc, '@group e2e') === false
        ) {
            $this->fail(sprintf(
                <<<'TXT'
Expected the test case "%s" to have the annotation `@group integration` as I/O operations have been
found in the file "%s".
TXT
                ,
                $testCaseClassName,
                $fileWithIoOperations
            ));
        }
    }
}
