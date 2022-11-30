<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

use _HumbugBoxb47773b41c19\GetOpt\ArgumentException\Unexpected;
class Arguments
{
    protected $arguments;
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }
    public function process(GetOpt $getopt, callable $setOption, callable $setCommand, callable $addOperand)
    {
        $operands = [];
        while (($arg = \array_shift($this->arguments)) !== null) {
            if ($this->isMeta($arg)) {
                $this->flushOperands($operands, $addOperand);
                $this->flushOperands($this->arguments, $addOperand);
                return \true;
            }
            if ($this->isValue($arg)) {
                $operands[] = $arg;
                if (\count($getopt->getOperands()) === 0 && ($command = $getopt->getCommand(\implode(' ', $operands)))) {
                    $setCommand($command);
                    $operands = [];
                }
            } else {
                $this->flushOperands($operands, $addOperand);
            }
            if ($this->isLongOption($arg)) {
                $setOption($this->longName($arg), function (Option $option = null) use($arg) {
                    return $this->value($arg, null, $option);
                });
                continue;
            }
            foreach ($this->shortNames($arg) as $name) {
                $requestedValue = \false;
                $setOption($name, function (Option $option = null) use($arg, $name, &$requestedValue) {
                    $requestedValue = \true;
                    return $this->value($arg, $name, $option);
                });
                if ($requestedValue) {
                    break;
                }
            }
        }
        $this->flushOperands($operands, $addOperand);
        return \true;
    }
    protected function flushOperands(array &$operands, callable $addOperand)
    {
        foreach ($operands as $operand) {
            $addOperand($operand);
        }
        $operands = [];
    }
    protected function isOption($arg)
    {
        return !$this->isValue($arg) && !$this->isMeta($arg);
    }
    protected function isValue($arg)
    {
        return empty($arg) || $arg === '-' || 0 !== \strpos($arg, '-');
    }
    protected function isMeta($arg)
    {
        return $arg && $arg === '--';
    }
    protected function isLongOption($arg)
    {
        return $this->isOption($arg) && $arg[1] === '-';
    }
    protected function longName($arg)
    {
        $name = \substr($arg, 2);
        $p = \strpos($name, '=');
        return $p ? \substr($name, 0, $p) : $name;
    }
    protected function shortNames($arg)
    {
        if (!$this->isOption($arg) || $this->isLongOption($arg)) {
            return [];
        }
        return \array_map(function ($i) use($arg) {
            return \mb_substr($arg, $i, 1);
        }, \range(1, \mb_strlen($arg) - 1));
    }
    protected function value($arg, $name = null, Option $option = null)
    {
        $p = \strpos($arg, $this->isLongOption($arg) ? '=' : $name);
        if ($this->isLongOption($arg) && $p || !$this->isLongOption($arg) && $p < \strlen($arg) - 1) {
            return \substr($arg, $p + 1);
        }
        if (!empty($this->arguments) && ($option && \in_array($option->getMode(), [GetOpt::REQUIRED_ARGUMENT, GetOpt::MULTIPLE_ARGUMENT]) || $this->isValue($this->arguments[0]))) {
            return \array_shift($this->arguments);
        }
        return null;
    }
    public static function fromString($argsString)
    {
        $argv = [''];
        $argsString = \trim($argsString);
        $argc = 0;
        if (empty($argsString)) {
            return new self([]);
        }
        $state = 'n';
        for ($i = 0; $i < \strlen($argsString); $i++) {
            $char = $argsString[$i];
            switch ($state) {
                case 'n':
                    if ($char === '\'') {
                        $state = 's';
                    } elseif ($char === '"') {
                        $state = 'd';
                    } elseif (\in_array($char, ["\n", "\t", ' '])) {
                        $argc++;
                        $argv[$argc] = '';
                    } else {
                        $argv[$argc] .= $char;
                    }
                    break;
                case 's':
                    if ($char === '\'') {
                        $state = 'n';
                    } elseif ($char === '\\') {
                        $i++;
                        $argv[$argc] .= $argsString[$i];
                    } else {
                        $argv[$argc] .= $char;
                    }
                    break;
                case 'd':
                    if ($char === '"') {
                        $state = 'n';
                    } elseif ($char === '\\') {
                        $i++;
                        $argv[$argc] .= $argsString[$i];
                    } else {
                        $argv[$argc] .= $char;
                    }
                    break;
            }
        }
        return new self($argv);
    }
}
