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

namespace Infection\Tests\TestFramework\PhpUnit\Config\Builder;

use Infection\Mutant\MutantInterface;
use Infection\MutationInterface;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use function Infection\Tests\normalizePath as p;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class MutationConfigBuilderTest extends TestCase
{
    public const HASH = 'a1b2c3';

    private $tmpDir;

    private $pathToProject;

    private $mutation;

    private $mutant;

    /**
     * @var MutationConfigBuilder
     */
    private $builder;

    private $xmlConfigurationHelper;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $workspace;

    protected function setUp(): void
    {
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);

        $this->fileSystem = new Filesystem();
        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);

        $this->pathToProject = p(realpath(__DIR__ . '/../../../../Fixtures/Files/phpunit/project-path'));

        $projectDir = '/project/dir';
        $phpunitXmlPath = __DIR__ . '/../../../../Fixtures/Files/phpunit/phpunit.xml';

        $this->mutation = $this->createMock(MutationInterface::class);
        $this->mutation
            ->method('getHash')
            ->willReturn(self::HASH);
        $this->mutation
            ->method('getOriginalFilePath')
            ->willReturn('/original/file/path');

        $this->mutant = $this->createMock(MutantInterface::class);
        $this->mutant
            ->method('getMutation')
            ->willReturn($this->mutation);
        $this->mutant
            ->method('getMutatedFilePath')
            ->willReturn('/mutated/file/path');

        $this->xmlConfigurationHelper = new XmlConfigurationHelper(new PathReplacer($this->fileSystem, $this->pathToProject), '');

        $this->builder = new MutationConfigBuilder(
            $this->tmpDir,
            file_get_contents($phpunitXmlPath),
            $this->xmlConfigurationHelper,
            $projectDir
        );
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->workspace);
    }

    public function test_it_builds_path_to_mutation_config_file(): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn([]);

        $this->assertSame(
            $this->tmpDir . '/phpunitConfiguration.a1b2c3.infection.xml',
            $this->builder->build($this->mutant)
        );
    }

    public function test_it_sets_custom_autoloader(): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $resultAutoLoaderFilePath = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $expectedCustomAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tmpDir,
            self::HASH
        );

        $this->assertSame($expectedCustomAutoloadFilePath, $resultAutoLoaderFilePath);
        $this->assertStringContainsString('app/autoload2.php', file_get_contents($expectedCustomAutoloadFilePath));
    }

    public function test_it_sets_custom_autoloader_when_attribute_is_absent(): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn([]);

        $phpunitXmlPath = __DIR__ . '/../../../../Fixtures/Files/phpunit/phpuit_without_bootstrap.xml';
        $this->builder = new MutationConfigBuilder(
            $this->tmpDir,
            file_get_contents($phpunitXmlPath),
            $this->xmlConfigurationHelper,
            'project/dir'
        );

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $resultAutoLoaderFilePath = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $expectedCustomAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tmpDir,
            self::HASH
        );

        $this->assertSame($expectedCustomAutoloadFilePath, $resultAutoLoaderFilePath);
        $this->assertStringContainsString('vendor/autoload.php', file_get_contents($expectedCustomAutoloadFilePath));
    }

    public function test_it_sets_stop_on_failure_flag(): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $value = $this->queryXpath($xml, '/phpunit/@stopOnFailure')[0]->nodeValue;

        $this->assertSame('true', $value);
    }

    public function test_it_sets_colors_flag(): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $value = $this->queryXpath($xml, '/phpunit/@colors')[0]->nodeValue;

        $this->assertSame('false', $value);
    }

    public function test_it_handles_root_test_suite(): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn([]);

        $phpunitXmlPath = __DIR__ . '/../../../../Fixtures/Files/phpunit/phpunit_root_test_suite.xml';
        $replacer = new PathReplacer($this->fileSystem, $this->pathToProject);
        $xmlConfigurationHelper = new XmlConfigurationHelper($replacer, '');

        $this->builder = new MutationConfigBuilder(
            $this->tmpDir,
            file_get_contents($phpunitXmlPath),
            $xmlConfigurationHelper,
            $this->pathToProject
        );

        $configurationPath = $this->builder->build($this->mutant);

        $this->assertSame(1, $this->queryXpath(file_get_contents($configurationPath), '/phpunit/testsuite')->length);
    }

    public function test_it_removes_original_loggers(): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);
        $nodeList = $this->queryXpath($xml, '/phpunit/logging/log[@type="coverage-html"]');

        $this->assertSame(0, $nodeList->length);
    }

    public function test_it_removes_printer_class(): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        /** @var \DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, '/phpunit/@printerClass');
        $this->assertSame(0, $filterNodes->length);
    }

    /**
     * @dataProvider coverageTestsProvider
     */
    public function test_it_sets_sorted_list_of_test_files(array $coverageTests, array $expectedFiles): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn($coverageTests);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $files = [];
        $nodes = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/file');

        foreach ($nodes as $node) {
            $files[] = $node->nodeValue;
        }

        $this->assertSame($expectedFiles, $files);
    }

    public function test_it_removes_default_test_suite(): void
    {
        $this->mutant
            ->method('getCoverageTests')
            ->willReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $value = $this->queryXpath($xml, '/phpunit/@defaultTestSuite');

        $this->assertCount(0, $value);
    }

    public function coverageTestsProvider(): array
    {
        return [
            [
                [
                    CoverageLineData::with(
                        'SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_calculates_percentage with data set #5',
                        '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                        0.086178
                    ),
                    CoverageLineData::with(
                        'SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_calculates_percentage with data set #6',
                        '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                        0.086178
                    ),
                    CoverageLineData::with(
                        'SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest::it_correctly_returns_id',
                        '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                        0.035935
                    ),
                    CoverageLineData::with(
                        'SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest::it_correctly_returns_recorded_at_date',
                        '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                        0.035935
                    ),
                ],
                [
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                ],
            ],
            [
                [
                    CoverageLineData::with(
                        'Path\\To\\A::test_a',
                        '/path/to/A.php',
                        0.186178
                    ),
                    CoverageLineData::with(
                        'Path\\To\\B::test_b',
                        '/path/to/B.php',
                        0.086178
                    ),
                    CoverageLineData::with(
                        'Path\\To\\C::test_c',
                        '/path/to/C.php',
                        0.016178
                    ),
                ],
                [
                    '/path/to/C.php',
                    '/path/to/B.php',
                    '/path/to/A.php',
                ],
            ],
        ];
    }

    private function queryXpath(string $xml, string $query)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xPath = new \DOMXPath($dom);

        return $xPath->query($query);
    }
}
