<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutant;

use Infection\Differ\Differ;
use Infection\Mutant\MutantCreator;
use Infection\Mutation;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpParser\PrettyPrinter\Standard;

class MutantCreatorTest extends MockeryTestCase
{
    const TEST_FILE_NAME = '/mutant.hash.infection.php';

    /**
     * @var string
     */
    private $directory;

    public function setUp()
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

    public function tearDown()
    {
        parent::tearDown();
        unlink($this->directory . self::TEST_FILE_NAME);
        rmdir($this->directory);
    }

    public function test_it_uses_avaialable_file_if_hash_is_the_same()
    {
        $standard = \Mockery::mock(Standard::class);
        $standard->shouldReceive('prettyPrintFile')->andReturn('The Print');

        $differ = \Mockery::mock(Differ::class);
        $differ->shouldReceive('diff')
            ->withArgs(['The Print', '<?php return \'This is a diff\';'])
            ->andReturn('This is the Diff');

        $mutation = \Mockery::mock(Mutation::class);
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
