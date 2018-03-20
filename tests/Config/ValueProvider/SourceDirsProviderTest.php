<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Config\ValueProvider\SourceDirsProvider;
use Mockery;

class SourceDirsProviderTest extends AbstractBaseProviderTest
{
    public function test_it_uses_guesser_and_default_value()
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            $this->markTestSkipped('Stty is not available');
        }

        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $sourceDirGuesser = $this->createMock(SourceDirGuesser::class);
        $sourceDirGuesser->method('guess')->willReturn(['src']);

        $provider = new SourceDirsProvider($consoleMock, $dialog, $sourceDirGuesser);

        $sourceDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            ['src']
        );

        $this->assertSame(['src'], $sourceDirs);
    }

    public function test_it_uses_guesser_and_non_default_guessed_value()
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $sourceDirGuesser = $this->createMock(SourceDirGuesser::class);
        $sourceDirGuesser->method('guess')->willReturn(['src/Namespace']);

        $provider = new SourceDirsProvider($consoleMock, $dialog, $sourceDirGuesser);

        $sourceDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            ['src']
        );

        $this->assertSame(['src/Namespace'], $sourceDirs);
    }

    public function test_it_uses_guesser_and_multiple_guessed_dirs()
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $sourceDirGuesser = $this->createMock(SourceDirGuesser::class);
        $sourceDirGuesser->method('guess')->willReturn(['foo', 'bar']);

        $provider = new SourceDirsProvider($consoleMock, $dialog, $sourceDirGuesser);

        $sourceDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            ['src']
        );

        $this->assertSame(['foo', 'bar'], $sourceDirs);
    }

    public function test_it_fills_choices_with_current_dir()
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $sourceDirGuesser = $this->createMock(SourceDirGuesser::class);
        $sourceDirGuesser->method('guess')->willReturn(['src']);

        $provider = new SourceDirsProvider($consoleMock, $dialog, $sourceDirGuesser);

        $sourceDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("0\n")),
            $this->createOutputInterface(),
            ['src']
        );

        $this->assertSame(['.'], $sourceDirs);
    }

    /**
     * @expectedException \LogicException
     */
    public function test_it_throws_exception_when_current_dir_is_selected_with_another_dir()
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $sourceDirGuesser = $this->createMock(SourceDirGuesser::class);
        $sourceDirGuesser->method('guess')->willReturn(['src']);

        $provider = new SourceDirsProvider($consoleMock, $dialog, $sourceDirGuesser);

        $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("0,1\n")),
            $this->createOutputInterface(),
            ['src']
        );
    }
}
