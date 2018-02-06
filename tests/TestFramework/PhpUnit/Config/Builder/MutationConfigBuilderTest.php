<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config\Builder;

use Infection\Finder\Locator;
use Infection\Mutant\Mutant;
use Infection\Mutation;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\Utils\TmpDirectoryCreator;
use Mockery;
use Symfony\Component\Filesystem\Filesystem;
use function Infection\Tests\normalizePath as p;

class MutationConfigBuilderTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    const HASH = 'a1b2c3';

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

    protected function setUp()
    {
        $this->workspace = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);

        $this->fileSystem = new Filesystem();
        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);

        $this->pathToProject = p(realpath(__DIR__ . '/../../../../Fixtures/Files/phpunit/project-path'));

        $projectDir = '/project/dir';
        $phpunitXmlPath = __DIR__ . '/../../../../Fixtures/Files/phpunit/phpunit.xml';

        $this->mutation = Mockery::mock(Mutation::class);
        $this->mutation->shouldReceive('getHash')->andReturn(self::HASH);
        $this->mutation->shouldReceive('getOriginalFilePath')->andReturn('/original/file/path');

        $this->mutant = Mockery::mock(Mutant::class);
        $this->mutant->shouldReceive('getMutation')->andReturn($this->mutation);
        $this->mutant->shouldReceive('getMutatedFilePath')->andReturn('/mutated/file/path');
        $this->mutant->shouldReceive('getMutatedFileCode')->andReturn('<?php');

        $replacer = new PathReplacer(new Locator([$this->pathToProject]));
        $this->xmlConfigurationHelper = new XmlConfigurationHelper($replacer);

        $this->builder = new MutationConfigBuilder(
            $this->tmpDir,
            file_get_contents($phpunitXmlPath),
            $this->xmlConfigurationHelper,
            $projectDir
        );
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->workspace);
    }

    public function test_it_builds_path_to_mutation_config_file()
    {
        $this->mutant->shouldReceive('getCoverageTests')->andReturn([]);

        $this->assertSame(
            $this->tmpDir . '/phpunitConfiguration.a1b2c3.infection.xml',
            $this->builder->build($this->mutant)
        );
    }

    public function test_it_sets_custom_autoloader()
    {
        $this->mutant->shouldReceive('getCoverageTests')->andReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $resultAutoLoaderFilePath = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $expectedCustomAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tmpDir,
            self::HASH
        );

        $this->assertSame($expectedCustomAutoloadFilePath, $resultAutoLoaderFilePath);
    }

    public function test_it_sets_custom_autoloader_when_attribute_is_absent()
    {
        $this->mutant->shouldReceive('getCoverageTests')->andReturn([]);
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
    }

    public function test_it_sets_stop_on_failure_flag()
    {
        $this->mutant->shouldReceive('getCoverageTests')->andReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $value = $this->queryXpath($xml, '/phpunit/@stopOnFailure')[0]->nodeValue;

        $this->assertSame('true', $value);
    }

    public function test_it_sets_colors_flag()
    {
        $this->mutant->shouldReceive('getCoverageTests')->andReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $value = $this->queryXpath($xml, '/phpunit/@colors')[0]->nodeValue;

        $this->assertSame('false', $value);
    }

    public function test_it_handles_root_test_suite()
    {
        $this->mutant->shouldReceive('getCoverageTests')->andReturn([]);

        $phpunitXmlPath = __DIR__ . '/../../../../Fixtures/Files/phpunit/phpunit_root_test_suite.xml';
        $replacer = new PathReplacer(new Locator([$this->pathToProject]));
        $xmlConfigurationHelper = new XmlConfigurationHelper($replacer);

        $this->builder = new MutationConfigBuilder(
            $this->tmpDir,
            file_get_contents($phpunitXmlPath),
            $xmlConfigurationHelper,
            $this->pathToProject
        );

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $this->assertEquals(1, $this->queryXpath($xml, '/phpunit/testsuite')->length);
    }

    public function test_it_removes_original_loggers()
    {
        $this->mutant->shouldReceive('getCoverageTests')->andReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);
        $nodeList = $this->queryXpath($xml, '/phpunit/logging/log[@type="coverage-html"]');

        $this->assertSame(0, $nodeList->length);
    }

    public function test_it_removes_printer_class()
    {
        $this->mutant->shouldReceive('getCoverageTests')->andReturn([]);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        /** @var \DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, '/phpunit/@printerClass');
        $this->assertSame(0, $filterNodes->length);
    }

    /**
     * @dataProvider coverageTestsProvider
     */
    public function test_it_sets_sorted_list_of_test_files(array $coverageTests, array $expectedFiles)
    {
        $this->mutant->shouldReceive('getCoverageTests')->andReturn($coverageTests);

        $configurationPath = $this->builder->build($this->mutant);

        $xml = file_get_contents($configurationPath);

        $files = [];
        $nodes = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/file');

        foreach ($nodes as $node) {
            $files[] = $node->nodeValue;
        }

        $this->assertSame($expectedFiles, $files);
    }

    public function coverageTestsProvider()
    {
        return [
            [
                [
                    [
                        'testMethod' => 'SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_calculates_percentage with data set #5',
                        'testFilePath' => '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                        'time' => 0.086178,
                    ],
                    [
                        'testMethod' => 'SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_calculates_percentage with data set #6',
                        'testFilePath' => '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                        'time' => 0.086178,
                    ],
                    [
                        'testMethod' => 'SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest::it_correctly_returns_id',
                        'testFilePath' => '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                        'time' => 0.035935,
                    ],
                    [
                        'testMethod' => 'SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest::it_correctly_returns_recorded_at_date',
                        'testFilePath' => '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                        'time' => 0.035935,
                    ],
                ],
                [
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                ],
            ],
            [
                [
                    [
                        'testMethod' => 'Path\\To\\A::test_a',
                        'testFilePath' => '/path/to/A.php',
                        'time' => 0.186178,
                    ],
                    [
                        'testMethod' => 'Path\\To\\B::test_b',
                        'testFilePath' => '/path/to/B.php',
                        'time' => 0.086178,
                    ],
                    [
                        'testMethod' => 'Path\\To\\C::test_c',
                        'testFilePath' => '/path/to/C.php',
                        'time' => 0.016178,
                    ],
                ],
                [
                    '/path/to/C.php',
                    '/path/to/B.php',
                    '/path/to/A.php',
                ],
            ],
        ];
    }

    protected function queryXpath(string $xml, string $query)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xPath = new \DOMXPath($dom);

        return $xPath->query($query);
    }
}
