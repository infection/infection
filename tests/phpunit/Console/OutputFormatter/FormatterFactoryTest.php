<?php

declare(strict_types=1);

namespace Infection\Tests\Console\OutputFormatter;

use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\FormatterFactory;
use Infection\Console\OutputFormatter\FormatterName;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\Tests\Fixtures\Console\FakeOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;
use function array_keys;
use function implode;
use function Safe\sprintf;

final class FormatterFactoryTest extends TestCase
{
    /**
     * @dataProvider formatterProvider
     */
    public function test_it_can_create_all_known_factories(
        string $formatterName,
        string $expectedFormatterClassName
    ): void
    {
        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->method('isDecorated')
            ->willReturn(false)
        ;

        $formatter = (new FormatterFactory($outputMock))->create($formatterName);

        $this->assertInstanceOf($expectedFormatterClassName, $formatter);
    }

    public static function formatterProvider(): iterable
    {
        $map = [
            FormatterName::DOT => DotFormatter::class,
            FormatterName::PROGRESS => ProgressFormatter::class,
        ];

        Assert::same(
            FormatterName::ALL,
            array_keys($map),
            sprintf(
                'Expected the given map to contain all the known formatters "%s". Got "%s"',
                implode('", "', FormatterName::ALL),
                implode('", "', array_keys($map))
            )
        );

        foreach ($map as $formatterName => $formatterClassName) {
            yield [$formatterName, $formatterClassName];
        }
    }
}
