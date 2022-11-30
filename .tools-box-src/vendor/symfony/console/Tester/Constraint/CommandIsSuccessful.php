<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Tester\Constraint;

use _HumbugBoxb47773b41c19\PHPUnit\Framework\Constraint\Constraint;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command;
final class CommandIsSuccessful extends Constraint
{
    public function toString() : string
    {
        return 'is successful';
    }
    protected function matches($other) : bool
    {
        return Command::SUCCESS === $other;
    }
    protected function failureDescription($other) : string
    {
        return 'the command ' . $this->toString();
    }
    protected function additionalFailureDescription($other) : string
    {
        $mapping = [Command::FAILURE => 'Command failed.', Command::INVALID => 'Command was invalid.'];
        return $mapping[$other] ?? \sprintf('Command returned exit status %d.', $other);
    }
}
