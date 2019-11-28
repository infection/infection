<?php
declare(strict_types=1);

namespace Codeception_With_Suite_Overridings;

class DatabaseTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testGetStuffWithoutLimit()
    {
        $config = $this->getModule('Db')->_getConfig();
        $database = new Database($config['dsn'], $config['user'], $config['password']);

        $this->assertCount(7, $database->getStuff(null));
    }

    public function testGetStuffWithLimit()
    {
        $config = $this->getModule('Db')->_getConfig();
        $database = new Database($config['dsn'], $config['user'], $config['password']);

        $this->assertCount(2, $database->getStuff(2));
    }
}
