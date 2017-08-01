<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);


namespace Infection\Tests\Mutant\Generator;

use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\TestFramework\Coverage\CodeCoverageData;
use \Mockery;
use PHPUnit\Framework\TestCase;

class MutationsGeneratorTest extends TestCase
{
    public function test_it_collects_plus_mutation()
    {
        $generator = $this->createMutationGenerator();

        $mutations = $generator->generate(false);

        $this->assertInstanceOf(Plus::class, $mutations[0]->getMutator());
    }

    public function test_it_collects_public_visibility_mutation()
    {
        $generator = $this->createMutationGenerator();

        $mutations = $generator->generate(false);

        $this->assertInstanceOf(PublicVisibility::class, $mutations[1]->getMutator());
    }

    public function test_it_can_skip_not_covered_on_file_level()
    {
        $generator = $this->createMutationGenerator(
            [
                'onlyCovered' => true,
                'hasTestsOnLine' => false,
                'hasTests' => false,
            ]
        );

        $mutations = $generator->generate(true);

        $this->assertCount(0, $mutations);
    }

    public function test_it_can_skip_not_covered_on_file_line_level()
    {
        $generator = $this->createMutationGenerator(
            [
                'onlyCovered' => true,
                'hasTestsOnLine' => false,
                'hasTests' => true,
            ]
        );

        $mutations = $generator->generate(true);

        $this->assertCount(0, $mutations);
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    private function createMutationGenerator(array $options = [])
    {
        $srcDirs = [
            dirname(__DIR__, 2) . '/Files/mutations/one-file',
        ];
        $excludedDirsOrFiles = [];

        $codeCoverageDataMock = Mockery::mock(CodeCoverageData::class);
        $codeCoverageDataMock->shouldReceive('hasTestsOnLine')->andReturn($options['hasTestsOnLine'] ?? true);

        if (isset($options['hasTests'])) {
            $codeCoverageDataMock->shouldReceive('hasTests')->andReturn($options['hasTests']);
        }

        return new MutationsGenerator($srcDirs, $excludedDirsOrFiles, $codeCoverageDataMock);
    }
}