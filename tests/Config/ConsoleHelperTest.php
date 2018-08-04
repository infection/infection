<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config;

use Infection\Config\ConsoleHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class ConsoleHelperTest extends TestCase
{
    public function test_it_writes_to_section(): void
    {
        $formatHelper = $this->createMock(FormatterHelper::class);
        $formatHelper->expects($this->once())
            ->method('formatBlock')
            ->with(
                'foo',
                'bg=blue;fg=white',
                true
            )
            ->willReturn('Formatted Foo');

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')->with(
                [
                    '',
                    'Formatted Foo',
                    '',
                ]
            );
        $console = new ConsoleHelper($formatHelper);

        $console->writeSection($output, 'foo');
    }

    public function test_get_question_with_no_default(): void
    {
        $consoleHelper = new ConsoleHelper(new FormatterHelper());

        $this->assertSame(
            '<info>Would you like a cup of tea?</info>: ',
            $consoleHelper->getQuestion('Would you like a cup of tea?')
        );
    }

    public function test_get_question_with_default(): void
    {
        $consoleHelper = new ConsoleHelper(new FormatterHelper());

        $this->assertSame(
            '<info>Would you like a cup of tea?</info> [<comment>yes</comment>]: ',
            $consoleHelper->getQuestion('Would you like a cup of tea?', 'yes')
        );
    }
}
