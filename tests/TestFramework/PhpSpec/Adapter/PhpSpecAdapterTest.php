<?php

declare(strict_types=1);

namespace TestFramework\PhpSpec\Adapter;


use Infection\Finder\AbstractExecutableFinder;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpSpec\Adapter\PhpSpecAdapter;
use Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder;
use PHPUnit\Framework\TestCase;
use Mockery;

class PhpUnitAdapterTest extends TestCase
{
    public function test_it_determines_when_tests_do_not_pass()
    {
        $output = <<<OUTPUT
TAP version 13
not ok 1 - Error: Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should find by user id
ok 1 - Infection\Application\Handler\AddViolationHandler: should add violation
ok 2 - Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should add goal
ok 3 - Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should remove existing one
ok 4 - Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should find by user id
not ok 103 - Error: Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should find by user id
1..103

OUTPUT;

        $adapter = $this->getAdapter();

        $this->assertFalse($adapter->testsPass($output));
    }

    public function test_it_determines_when_tests_pass()
    {
        $output = <<<OUTPUT
TAP version 13
ok 1 - Infection\Application\Handler\AddViolationHandler: should add violation
ok 2 - Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should add goal
ok 3 - Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should remove existing one
ok 4 - Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should find by user id
1..4

OUTPUT;

        $adapter = $this->getAdapter();

        $this->assertTrue($adapter->testsPass($output));
    }

    public function test_it_catches_fatal_errors()
    {
        $output = <<<OUTPUT
TAP version 13
ok 1 - Infection\Application\Handler\AddViolationHandler: should add violation
ok 2 - Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should add goal
ok 3 - Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should remove existing one
ok 4 - Infection\Infrastructure\Domain\Model\Goal\InMemoryGoalRepository: should find by user id
Fatal error happened .....
1..5

OUTPUT;

        $adapter = $this->getAdapter();

        $this->assertFalse($adapter->testsPass($output));
    }

    private function getAdapter(): PhpSpecAdapter
    {
        $executableFined = Mockery::mock(AbstractExecutableFinder::class);
        $initialConfigBuilder = Mockery::mock(InitialConfigBuilder::class);
        $mutationConfigBuilder = Mockery::mock(MutationConfigBuilder::class);
        $cliArgumentsBuilder = Mockery::mock(CommandLineArgumentsAndOptionsBuilder::class);

        return new PhpSpecAdapter($executableFined, $initialConfigBuilder, $mutationConfigBuilder, $cliArgumentsBuilder);
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}