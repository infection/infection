<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\InfectionConfig;
use Infection\Config\ValueProvider\TimeoutProvider;
use Mockery;

/**
 * @internal
 */
final class TimeoutProviderTest extends AbstractBaseProviderTest
{
    public function test_it_uses_default_value()
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new TimeoutProvider($consoleMock, $dialog);

        $timeout = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface()
        );

        $this->assertSame(InfectionConfig::PROCESS_TIMEOUT_SECONDS, $timeout);
    }

    public function test_it_casts_any_value_to_integer()
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new TimeoutProvider($consoleMock, $dialog);

        $timeout = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("13\n")),
            $this->createOutputInterface()
        );

        $this->assertSame(13, $timeout);
    }

    /**
     * @dataProvider validatorProvider
     * @expectedException \RuntimeException
     */
    public function test_it_does_not_allow_invalid_values($inputValue)
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new TimeoutProvider($consoleMock, $dialog);

        $timeout = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("{$inputValue}\n")),
            $this->createOutputInterface()
        );

        $this->assertSame(InfectionConfig::PROCESS_TIMEOUT_SECONDS, $timeout);
    }

    public function validatorProvider()
    {
        return [
            ['str'],
            [0],
            [-1],
            [0.1],
        ];
    }
}
