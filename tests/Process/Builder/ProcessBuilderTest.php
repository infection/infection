<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Builder;

use Infection\Mutant\MutantInterface;
use Infection\Process\Builder\ProcessBuilder;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Mockery;

/**
 * @internal
 */
final class ProcessBuilderTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function test_getProcessForInitialTestRun_has_no_timeout()
    {
        $fwAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);
        $fwAdapter->shouldReceive('getCommandLine', ['buildInitialConfigFile'])->andReturn(['/usr/bin/php']);
        $fwAdapter->shouldReceive('buildInitialConfigFile')->andReturn('buildInitialConfigFile');

        $builder = new ProcessBuilder($fwAdapter, 100);

        $process = $builder->getProcessForInitialTestRun('', false);

        $this->assertContains('/usr/bin/php', $process->getCommandLine());
        $this->assertNull($process->getTimeout());
    }

    public function test_getProcessForMutant_has_timeout()
    {
        $fwAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);
        $fwAdapter->shouldReceive('getCommandLine', ['buildMutationConfigFile'])->andReturn(['/usr/bin/php']);
        $fwAdapter->shouldReceive('buildMutationConfigFile')->andReturn('buildMutationConfigFile');

        $builder = new ProcessBuilder($fwAdapter, 100);

        $process = $builder->getProcessForMutant(Mockery::mock(MutantInterface::class))->getProcess();

        $this->assertContains('/usr/bin/php', $process->getCommandLine());
        $this->assertSame(100.0, $process->getTimeout());
    }
}
