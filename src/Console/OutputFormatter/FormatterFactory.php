<?php

declare(strict_types=1);

namespace Infection\Console\OutputFormatter;

use LogicException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;
use function implode;
use function Safe\sprintf;

final class FormatterFactory
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function create(string $formatterName): OutputFormatter
    {
        Assert::oneOf(
            $formatterName,
            FormatterName::ALL,
            sprintf(
                'Unknown formatter "%%s". The known formatters are: "%s"',
                implode('", "', FormatterName::ALL)
            )
        );

        switch ($formatterName) {
            case FormatterName::PROGRESS:
                return new ProgressFormatter(new ProgressBar($this->output));

            case FormatterName::DOT:
                return new DotFormatter($this->output);
        }

        throw new LogicException('Unreachable statement');
    }
}
