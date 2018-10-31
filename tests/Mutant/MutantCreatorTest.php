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

namespace Infection\Tests\Mutant;

use Infection\Differ\Differ;
use Infection\Mutant\MutantCreator;
use Infection\MutationInterface;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpParser\PrettyPrinter\Standard;

/**
 * @internal
 */
final class MutantCreatorTest extends MockeryTestCase
{
    private const TEST_FILE_NAME = '/mutant.hash.infection.php';

    /**
     * @var string
     */
    private $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = \sys_get_temp_dir() . '/infection/MutantCreator';
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

    public function test_it_uses_avaialable_file_if_hash_is_the_same(): void
    {
        $standard = \Mockery::mock(Standard::class);
        $standard->shouldReceive('prettyPrintFile')->andReturn('The Print');

        $differ = \Mockery::mock(Differ::class);
        $differ->shouldReceive('diff')
            ->withArgs(['The Print', '<?php return \'This is a diff\';'])
            ->andReturn('This is the Diff');

        $mutation = \Mockery::mock(MutationInterface::class);
        $mutation->shouldReceive('getHash')->andReturn('hash');
        $mutation->shouldReceive('getOriginalFilePath')->andReturn('original/path');
        $mutation->shouldReceive('getOriginalFileAst')->andReturn(['ast']);
        $mutation->shouldReceive('getAttributes')->andReturn(['startLine' => 1]);
        $mutation->shouldReceive('isOnFunctionSignature')->andReturn(true);
        $mutation->shouldReceive('isCoveredByTest')->once()->andReturn(true);

        $coverage = \Mockery::mock(CodeCoverageData::class);
        $coverage->shouldReceive('hasExecutedMethodOnLine')->andReturn(true);
        $coverage->shouldReceive('getAllTestsFor')->andReturn(['test', 'list']);

        $creator = new MutantCreator($this->directory, $differ, $standard);
        $mutant = $creator->create($mutation, $coverage);

        $this->assertSame($this->directory . self::TEST_FILE_NAME, $mutant->getMutatedFilePath());
        $this->assertSame('This is the Diff', $mutant->getDiff());
        $this->assertTrue($mutant->isCoveredByTest());
        $this->assertSame(['test', 'list'], $mutant->getCoverageTests());
        $this->assertSame('hash', $mutant->getMutation()->getHash());
    }
}
