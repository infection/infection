<?php declare(strict_types=1);

namespace Infection\Tests\Process\Builder;

use Infection\Mutant\Mutant;
use Infection\Process\Builder\ProcessBuilder;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use PHPUnit\Framework\TestCase;


class ProcessBuilderTest extends TestCase
{
    public function test_getProcessForInitialTestRun_has_no_timeout()
    {
        $fwAdapter = \Mockery::mock(AbstractTestFrameworkAdapter::class);
        $fwAdapter->shouldReceive('getExecutableCommandLine', ['buildInitialConfigFile'])->andReturn('getExecutableCommandLine');
        $fwAdapter->shouldReceive('buildInitialConfigFile')->andReturn('buildInitialConfigFile');

        $builder =  new ProcessBuilder($fwAdapter, 100);

        $process = $builder->getProcessForInitialTestRun();

        $this->assertContains('getExecutableCommandLine', $process->getCommandLine());
        $this->assertNull($process->getTimeout());
    }

    public function test_getProcessForMutant_has_timeout()
    {
        $fwAdapter = \Mockery::mock(AbstractTestFrameworkAdapter::class);
        $fwAdapter->shouldReceive('getExecutableCommandLine', ['buildMutationConfigFile'])->andReturn('getExecutableCommandLine');
        $fwAdapter->shouldReceive('buildMutationConfigFile')->andReturn('buildMutationConfigFile');

        $builder =  new ProcessBuilder($fwAdapter, 100);

        $process = $builder->getProcessForMutant(\Mockery::mock(Mutant::class))->getProcess();

        $this->assertContains('getExecutableCommandLine', $process->getCommandLine());
        $this->assertSame(100.0, $process->getTimeout());
    }

    protected function tearDown()
    {
        \Mockery::close();
    }
}