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
use Infection\Mutant\MutantCreator;
use Infection\Mutation;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use function sys_get_temp_dir;

/**
 * @group integration Requires some I/O operations & writes
 */
final class MutantCreatorTest extends TestCase
{
    private const TEST_FILE_NAME = '/mutant.hash.infection.php';

    /**
     * @var string
     */
    private $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/infection/MutantCreator';
        mkdir($this->directory, 0777, true);
        touch($this->directory . self::TEST_FILE_NAME);

        file_put_contents($this->directory . self::TEST_FILE_NAME,
            <<<PHP
<?php return 'This is a diff';
PHP
);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unlink($this->directory . self::TEST_FILE_NAME);
        rmdir($this->directory);
    }

    public function test_it_uses_available_file_if_hash_is_the_same(): void
    {
        $standard = $this->createMock(Standard::class);
        $standard->method('prettyPrintFile')
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

        $creator = new MutantCreator($this->directory, $differ, $standard);
        $mutant = $creator->create($mutation);

        $this->assertSame($this->directory . self::TEST_FILE_NAME, $mutant->getMutantFilePath());
        $this->assertSame('This is the Diff', $mutant->getDiff());
        $this->assertTrue($mutant->isCoveredByTest());
        $this->assertSame(['test', 'list'], $mutant->getTests());
        $this->assertSame('hash', $mutant->getMutation()->getHash());
    }
}
