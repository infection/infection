<?php
/**
 * Created by PhpStorm.
 * User: fenikkusu
 * Date: 3/30/18
 * Time: 8:33 PM
 */

namespace Infection\Tests\TestFramework\Config;

use Infection\Config\InfectionConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class InfectionConfigTest extends TestCase
{
    /** @var InfectionConfig */
    private $testSubject = null;

    /** @var Filesystem */
    private $mockFilesystem = null;

    public function setUp()
    {
        parent::setUp();

        $this->mockFilesystem = $this->getMockBuilder(Filesystem::class)
            ->getMock();
    }

    /**
     * @dataProvider dpConfig
     * @param \stdClass $config Settings
     * @param string $testFramework Correct Testing Framework
     */
    public function test_config(\stdClass $config, string $methodName, string $result)
    {
        $testSubject = new InfectionConfig(
            $config,
            $this->mockFilesystem,
            ''
        );

        $this->assertEquals($result, $testSubject->{$methodName}());
    }

    public function dpConfig()
    {
        return [
            [
                (object) [
                    'system' => []
                ],
                'getTestFramework',
                'phpunit'
            ],
            [
                (object) [
                    'system' => (object) [
                        'testFramework' => 'phpspec'
                    ]
                ],
                'getTestFramework',
                'phpspec'
            ],
            [
                (object) [
                    'system' => []
                ],
                'getBootstrap',
                ''
            ],
            [
                (object) [
                    'system' => (object) [
                        'bootstrap' => 'bootstrap.php'
                    ]
                ],
                'getBootstrap',
                'bootstrap.php'
            ]
        ];
    }
}
