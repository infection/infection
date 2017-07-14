<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config;

use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\MutationXmlConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class MutationXmlConfigurationTest extends AbstractXmlConfiguration
{
    private $customAutoloadConfigPath = '/custom/path/autoload.php';

    protected function getConfigurationObject(array $coverageTests = [])
    {
        $phpunitXmlPath = __DIR__ . '/../../../Files/phpunit/phpunit.xml';

        $replacer = new PathReplacer(new Locator($this->pathToProject));

        return new MutationXmlConfiguration(
            $this->tempDir,
            $phpunitXmlPath,
            $replacer,
            $this->customAutoloadConfigPath,
            $coverageTests
        );
    }

    public function test_it_sets_custom_autoloader()
    {
        $xml = $this->configuration->getXml();

        $value = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $this->assertSame($this->customAutoloadConfigPath, $value);
    }

    public function test_it_sets_stop_on_failure_flag()
    {
        $xml = $this->configuration->getXml();

        $value = $this->queryXpath($xml, '/phpunit/@stopOnFailure')[0]->nodeValue;

        $this->assertSame('true', $value);
    }

    public function test_it_sets_colors_flag()
    {
        $xml = $this->configuration->getXml();

        $value = $this->queryXpath($xml, '/phpunit/@colors')[0]->nodeValue;

        $this->assertSame('false', $value);
    }

    /**
     * @dataProvider coverageTestsProvider
     */
    public function test_it_sets_sorted_list_of_test_files(array $coverageTests, array $expectedFiles)
    {
        $configuration = $this->getConfigurationObject($coverageTests);
        $xml = $configuration->getXml();

        $files = [];
        $nodes = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/file');

        foreach ($nodes as $node) {
            $files[] = $node->nodeValue;
        }

        $this->assertSame($expectedFiles, $files);
    }

    public function test_it_handles_root_test_suite()
    {
        $phpunitXmlPath = __DIR__ . '/../../../Files/phpunit/phpunit_root_test_suite.xml';

        $replacer = new PathReplacer(new Locator($this->pathToProject));

        $configuration = new MutationXmlConfiguration(
            $this->tempDir,
            $phpunitXmlPath,
            $replacer,
            $this->customAutoloadConfigPath,
            []
        );

        $xml = $configuration->getXml();

        $this->assertEquals(1, $this->queryXpath($xml, '/phpunit/testsuite')->length);
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
                ]
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
                ]
            ]
        ];
    }

}
