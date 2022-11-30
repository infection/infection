<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console\OutputFormatter;

use function implode;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\ProgressBar;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class FormatterFactory
{
    public function __construct(private OutputInterface $output)
    {
    }
    public function create(string $formatterName) : OutputFormatter
    {
        Assert::oneOf($formatterName, FormatterName::ALL, sprintf('Unknown formatter %%s. The known formatters are: "%s"', implode('", "', FormatterName::ALL)));
        return match ($formatterName) {
            FormatterName::PROGRESS => new ProgressFormatter(new ProgressBar($this->output)),
            FormatterName::DOT => new DotFormatter($this->output),
        };
    }
}
