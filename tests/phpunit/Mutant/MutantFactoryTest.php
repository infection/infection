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

namespace Infection\Tests\Mutant;

use Infection\Differ\Differ;
use Infection\Mutant\MutantCodeFactory;
use Infection\Mutant\MutantFactory;
use Infection\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\TestCase;
use function sys_get_temp_dir;
use PhpParser\Node;

/**
 * @group integration Requires some I/O operations
 */
final class MutantCreatorTest extends TestCase
/**
 * @group integration Requires I/O reads & writes
 */
final class MutantFactoryTest extends FileSystemTestCase
{
    private const TEST_FILE_NAME = 'mutant.hash.infection.php';

    /**
     * @var string
     */
    private $directory;

    protected function setUp(): void
    {
        file_put_contents(
            $this->tmp .'/'. self::TEST_FILE_NAME,
            <<<PHP
<?php return 'This is a diff';
PHP
        );
    }

    public function test_it_creates_a_mutant_instance_from_the_given_mutation(): void
    {
        $printerMock = $this->createMock(PrettyPrinterAbstract::class);
        $printerMock
            ->method('prettyPrintFile')
            ->willReturn('The Print')
        ;

        $differMock = $this->createMock(Differ::class);
        $differMock
            ->method('diff')
            ->with('The Print', '<?php return \'This is a diff\';')
            ->willReturn('This is the Diff')
        ;

        $mutantCodeFactoryMock = $this->createMock(MutantCodeFactory::class);

        $mutation = new Mutation(
            '/path/to/acme/Foo.php',
            [new Node\Stmt\Namespace_(
                new Node\Name('Acme'),
                [new Node\Scalar\LNumber(0)]
            )],
            Plus::getName(),
            [
                'startLine' => 3,
                'endLine' => 5,
                'startTokenPos' => 21,
                'endTokenPos' => 31,
                'startFilePos' => 43,
                'endFilePos' => 53,
            ],
            Node\Scalar\LNumber::class,
            new Node\Scalar\LNumber(1),
            0,
            [
                CoverageLineData::with(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    0.01
                ),
            ]
        );

        $mutantFactory = new MutantFactory(
            $this->tmp,
            $differMock,
            $printerMock,
            $mutantCodeFactoryMock
        );

        $mutant = ($mutantFactory)->create($mutation);

        $this->assertSame($this->directory . self::TEST_FILE_NAME, $mutant->getMutantFilePath());
        $this->assertSame('This is the Diff', $mutant->getDiff());
        $this->assertTrue($mutant->isCoveredByTest());
        $this->assertSame(['test', 'list'], $mutant->getTests());
        $this->assertSame('hash', $mutant->getMutation()->getHash());
    }

    public function test_it_uses_available_file_if_hash_is_the_same(): void
    {
        $standard = $this->createMock(Standard::class);
        $standard
            ->method('prettyPrintFile')
            ->willReturn('The Print');

        $differ = $this->createMock(Differ::class);
        $differ->method('diff')
            ->with('The Print', '<?php return \'This is a diff\';')
            ->willReturn('This is the Diff');

        $mutation = $this->createMock(Mutation::class);
        $mutation->method('getHash')->willReturn('hash');
        $mutation->method('getOriginalFilePath')->willReturn('original/path');
        $mutation->method('getOriginalFileAst')->willReturn(['ast']);
        $mutation->method('getAttributes')->willReturn(['startLine' => 1]);
        $mutation->method('getAllTests')->willReturn(['test', 'list']);

        $mutation->expects($this->once())->method('isCoveredByTest')->willReturn(true);

        $creator = new MutantFactory($this->directory, $differ, $standard);
        $mutant = $creator->create($mutation);

        $this->assertSame($this->directory . self::TEST_FILE_NAME, $mutant->getMutantFilePath());
        $this->assertSame('This is the Diff', $mutant->getDiff());
        $this->assertTrue($mutant->isCoveredByTest());
        $this->assertSame(['test', 'list'], $mutant->getTests());
        $this->assertSame('hash', $mutant->getMutation()->getHash());
    }
}
