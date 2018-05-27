<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutant;

use Infection\Mutant\Mutant;
use Infection\MutationInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutantTest extends TestCase
{
    public function test_it_passes_along_its_input_without_changing_it()
    {
        $filepath = 'path/to/file';
        $mutation = $this->createMock(MutationInterface::class);
        $mutation->expects($this->never())->method($this->anything());
        $diff = 'diff string';
        $isCoveredByTest = true;
        $coverageTests = ['tests'];

        $mutant = new Mutant(
            $filepath,
            $mutation,
            $diff,
            $isCoveredByTest,
            $coverageTests
        );
        $this->assertSame($filepath, $mutant->getMutatedFilePath());
        $this->assertSame($mutation, $mutant->getMutation());
        $this->assertSame($diff, $mutant->getDiff());
        $this->assertSame($isCoveredByTest, $mutant->isCoveredByTest());
        $this->assertSame($coverageTests, $mutant->getCoverageTests());
    }
}
