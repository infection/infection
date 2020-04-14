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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Infection\Tests\AutoReview\IntegrationGroup\IoCodeDetector
 */
final class IoCodeDetectorTest extends TestCase
{
    /**
     * @dataProvider codeProvider
     */
    public function test_it_can_detect_io_operations(string $code, bool $expected): void
    {
        $actual = IoCodeDetector::codeContainsIoOperations($code);

        $this->assertSame($expected, $actual);
    }

    public function codeProvider(): iterable
    {
        yield 'empty' => [
            '',
            false,
        ];

        yield 'core function' => [
            <<<'PHP'
<?php
echo basename('/etc/sudoers.d', '.d');
PHP
            ,
            false,  // Cannot detect this one since the call is not fully-qualified and there is no
                    // use statements - too tricky to detect
        ];

        yield 'core function - use statement' => [
            <<<'PHP'
<?php

use function basename;

echo basename('/etc/sudoers.d', '.d');
PHP
            ,
            true,
        ];

        yield 'core function - fully-qualified call' => [
            <<<'PHP'
<?php

echo \basename('/etc/sudoers.d', '.d');
PHP
            ,
            true,
        ];

        yield 'Symfony FileSystem - use statement' => [
            <<<'PHP'
<?php

use Symfony\Component\Filesystem\Filesystem;

(new Filesystem)->dumpFile('foo.php', '');
PHP
            ,
            true,
        ];

        yield 'Symfony FileSystem - FQCN' => [
            <<<'PHP'
<?php

echo \Symfony\Component\Filesystem\Filesystem::class;
PHP
            ,
            false,
        ];

        yield 'Safe file-system function' => [
            <<<'PHP'
<?php

use function Safe\getcwd;

getcwd();
PHP
            ,
            true,
        ];

        yield 'Safe file-system function as fully-qualified call' => [
            <<<'PHP'
<?php

\Safe\rename('foo', 'bar');
PHP
            ,
            true,
        ];

        yield 'Safe non-file-system function' => [
            <<<'PHP'
<?php

use function Safe\sprintf();

sprintf('%s', 'foo');
PHP
            ,
            false,
        ];

        yield 'Statement containing a word match of a FS function' => [
            <<<'PHP'
<?php

/**
 * copyright
 */
PHP
            ,
            false,
        ];
    }
}
