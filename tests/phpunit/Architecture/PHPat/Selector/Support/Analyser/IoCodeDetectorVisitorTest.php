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

namespace Infection\Tests\Architecture\PHPat\Selector\Support\Analyser;

use Infection\Testing\SingletonContainer;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IoCodeDetectorVisitor::class)]
final class IoCodeDetectorVisitorTest extends TestCase
{
    #[DataProvider('codeProvider')]
    public function test_it_can_detect_io_operations(
        string $code,
        bool $expected,
    ): void {
        $visitor = IoCodeDetectorVisitor::create();
        $nodes = SingletonContainer::getContainer()->getParser()->parse($code);
        $traverser = new NodeTraverser($visitor);

        $traverser->traverse($nodes ?? []);
        $actual = $visitor->hasIoOperations();

        $this->assertSame($expected, $actual);
    }

    public static function codeProvider(): iterable
    {
        yield 'empty' => [
            '',
            false,
        ];

        yield 'core function' => [
            <<<'PHP'
                <?php
                echo basename('/etc/sudoers.d', '.d');
                PHP,
            false,  // Cannot detect this one since the call is not fully-qualified and there is no use statements - too tricky to detect
        ];

        yield 'core function - use statement' => [
            <<<'PHP'
                <?php

                use function basename;

                echo basename('/etc/sudoers.d', '.d');
                PHP,
            true,
        ];

        yield 'core function - fully-qualified call' => [
            <<<'PHP'
                <?php

                echo \basename('/etc/sudoers.d', '.d');
                PHP,
            true,
        ];

        yield 'fdatasync core function added in PHP 8.1' => [
            <<<'PHP'
                <?php

                \fdatasync($stream);
                PHP,
            true,
        ];

        yield 'fsync core function added in PHP 8.1' => [
            <<<'PHP'
                <?php

                \fsync($stream);
                PHP,
            true,
        ];

        yield 'I/O-related core function added in PHP 8.3' => [
            <<<'PHP'
                <?php

                use function stream_context_set_options;

                stream_context_set_options($context, []);
                PHP,
            true,
        ];

        yield 'directory core function' => [
            <<<'PHP'
                <?php

                \scandir(__DIR__);
                PHP,
            true,
        ];

        yield 'cURL core function' => [
            <<<'PHP'
                <?php

                \curl_exec($handle);
                PHP,
            true,
        ];

        yield 'stream socket core function' => [
            <<<'PHP'
                <?php

                \stream_socket_client('tcp://127.0.0.1:80');
                PHP,
            true,
        ];

        yield 'HTTP output core function' => [
            <<<'PHP'
                <?php

                \header('Content-Type: text/plain');
                PHP,
            true,
        ];

        yield 'XMLReader URI factory method' => [
            <<<'PHP'
                <?php

                \XMLReader::fromUri('file.xml');
                PHP,
            true,
        ];

        yield 'XMLWriter URI core function' => [
            <<<'PHP'
                <?php

                \xmlwriter_open_uri('file.xml');
                PHP,
            true,
        ];

        yield 'I/O-related core function added in PHP 8.4' => [
            <<<'PHP'
                <?php

                \request_parse_body();
                PHP,
            true,
        ];

        yield 'cURL-related core function added in PHP 8.5' => [
            <<<'PHP'
                <?php

                \curl_share_init_persistent([]);
                PHP,
            true,
        ];

        yield 'cURL multi core function added in PHP 8.5' => [
            <<<'PHP'
                <?php

                \curl_multi_get_handles($multiHandle);
                PHP,
            true,
        ];

        yield 'file-cache-related core function added in PHP 8.5' => [
            <<<'PHP'
                <?php

                \opcache_is_script_cached_in_file_cache(__FILE__);
                PHP,
            true,
        ];

        yield 'Symfony FileSystem import' => [
            <<<'PHP'
                <?php

                use Symfony\Component\Filesystem\Filesystem;
                PHP,
            false,
        ];

        yield 'Symfony FileSystem instantiation' => [
            <<<'PHP'
                <?php

                use Symfony\Component\Filesystem\Filesystem;

                (new Filesystem)->dumpFile('foo.php', '');
                PHP,
            true,
        ];

        yield 'Symfony FileSystem alias instantiation' => [
            <<<'PHP'
                <?php

                use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

                (new SymfonyFilesystem)->dumpFile('foo.php', '');
                PHP,
            true,
        ];

        yield 'Symfony FileSystem group use instantiation' => [
            <<<'PHP'
                <?php

                use Symfony\Component\Filesystem\{Filesystem};

                (new Filesystem)->dumpFile('foo.php', '');
                PHP,
            true,
        ];

        yield 'Symfony FileSystem mock' => [
            <<<'PHP'
                <?php

                use Symfony\Component\Filesystem\Filesystem;

                $this->createMock(Filesystem::class);
                PHP,
            false,
        ];

        yield 'Infection FileSystem instantiation' => [
            <<<'PHP'
                <?php

                use Infection\FileSystem\FileSystem;

                (new FileSystem)->readFile('foo.php');
                PHP,
            true,
        ];

        yield 'InMemoryFileSystem instantiation' => [
            <<<'PHP'
                <?php

                use Infection\FileSystem\InMemoryFileSystem;

                (new InMemoryFileSystem())->readFile('foo.php');
                PHP,
            false,
        ];

        yield 'FakeFileSystem instantiation' => [
            <<<'PHP'
                <?php

                use Infection\FileSystem\FakeFileSystem;

                (new FakeFileSystem())->readFile('foo.php');
                PHP,
            false,
        ];

        yield 'DummyFileSystem instantiation' => [
            <<<'PHP'
                <?php

                use Infection\FileSystem\DummyFileSystem;

                (new DummyFileSystem())->readFile('foo.php');
                PHP,
            false,
        ];

        yield 'Symfony FileSystem - FQCN' => [
            <<<'PHP'
                <?php

                echo \Symfony\Component\Filesystem\Filesystem::class;
                PHP,
            false,
        ];

        yield 'static FS utility class reference' => [
            <<<'PHP'
                <?php

                echo \Infection\Tests\TestingUtility\FS::class;
                PHP,
            false,
        ];

        yield 'static FS utility method call' => [
            <<<'PHP'
                <?php

                \Infection\Tests\TestingUtility\FS::tmpDir('test');
                PHP,
            true,
        ];

        yield 'static FS utility imported method call' => [
            <<<'PHP'
                <?php

                use Infection\Tests\TestingUtility\FS;

                FS::tmpFile('test');
                PHP,
            true,
        ];

        yield 'static FS utility group use method call' => [
            <<<'PHP'
                <?php

                use Infection\Tests\TestingUtility\{FS};

                FS::tmpFile('test');
                PHP,
            true,
        ];

        yield 'Safe file-system function' => [
            <<<'PHP'
                <?php

                use function Safe\getcwd;

                getcwd();
                PHP,
            true,
        ];

        yield 'Safe file-system group use function' => [
            <<<'PHP'
                <?php

                use function Safe\{getcwd};

                getcwd();
                PHP,
            true,
        ];

        yield 'Safe directory function' => [
            <<<'PHP'
                <?php

                use function Safe\getcwd;

                getcwd();
                PHP,
            true,
        ];

        yield 'Safe variant of native function' => [
            <<<'PHP'
                <?php

                use function Safe\basename;

                basename('/etc/sudoers.d', '.d');
                PHP,
            true,
        ];

        yield 'directory function - use statement' => [
            <<<'PHP'
                <?php

                use function opendir;

                opendir(__DIR__);
                PHP,
            true,
        ];

        yield 'Safe file-system function as fully-qualified call' => [
            <<<'PHP'
                <?php

                \Safe\rename('foo', 'bar');
                PHP,
            true,
        ];

        yield 'Safe non-file-system function' => [
            <<<'PHP'
                <?php

                use function Safe\sprintf;

                sprintf('%s', 'foo');
                PHP,
            false,
        ];

        yield 'Statement containing a word match of a FS function' => [
            <<<'PHP'
                <?php

                /**
                 * copyright
                 */
                PHP,
            false,
        ];
    }
}
