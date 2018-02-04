<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\TextLogFileProvider;
use Mockery;

class TextLogFileProviderTest extends AbstractBaseProviderTest
{
    public function test_it_uses_default_value()
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new TextLogFileProvider($consoleMock, $dialog);

        $textLogFilePath = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            []
        );

        $this->assertSame(TextLogFileProvider::TEXT_LOG_FILE_NAME, $textLogFilePath);
    }

    public function test_it_uses_typed_value()
    {
        $inputValue = 'test-log.txt';
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new TextLogFileProvider($consoleMock, $dialog);

        $textLogFilePath = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("{$inputValue}\n")),
            $this->createOutputInterface(),
            []
        );

        $this->assertSame($inputValue, $textLogFilePath);
    }
}
