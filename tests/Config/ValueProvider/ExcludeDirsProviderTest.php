<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\ExcludeDirsProvider;
use Infection\Config\ValueProvider\SourceDirsProvider;
use Mockery;

class ExcludeDirsProviderTest extends AbstractBaseProviderTest
{
    public function test_it_contains_vendors_when_sources_contains_current_dir()
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new ExcludeDirsProvider($consoleMock, $dialog);

        $excludeDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            ['src'],
            ['.']
        );

        $this->assertContains('vendor', $excludeDirs);
    }

    public function test_it_validates_dirs()
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped("Stty is not available");
        }

        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new ExcludeDirsProvider($consoleMock, $dialog);

        $excludeDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("abc\n")),
            $this->createOutputInterface(),
            ['src'],
            ['src']
        );

        $this->assertCount(0, $excludeDirs);
    }

    public function test_passes_when_correct_dir_typed()
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped("Stty is not available");
        }

        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new ExcludeDirsProvider($consoleMock, $dialog);

        $excludeDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("Files\n")),
            $this->createOutputInterface(),
            ['src'],
            ['tests']
        );

        $this->assertContains('Files', $excludeDirs);
    }
}