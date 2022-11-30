<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

use _HumbugBoxb47773b41c19\GetOpt\ArgumentException\Missing;
use _HumbugBoxb47773b41c19\GetOpt\ArgumentException\Unexpected;
class GetOpt implements \Countable, \ArrayAccess, \IteratorAggregate
{
    const NO_ARGUMENT = ':noArg';
    const REQUIRED_ARGUMENT = ':requiredArg';
    const OPTIONAL_ARGUMENT = ':optionalArg';
    const MULTIPLE_ARGUMENT = ':multipleArg';
    const SETTING_SCRIPT_NAME = 'scriptName';
    const SETTING_DEFAULT_MODE = 'defaultMode';
    const SETTING_STRICT_OPTIONS = 'strictOptions';
    const SETTING_STRICT_OPERANDS = 'strictOperands';
    use WithOptions {
        getOption as getOptionObject;
        getOptions as getOptionObjects;
    }
    use WithOperands {
        getOperand as getOperandObject;
        getOperands as getOperandObjects;
    }
    use WithMagicGetter;
    protected $help;
    protected $settings = [self::SETTING_STRICT_OPTIONS => \true, self::SETTING_STRICT_OPERANDS => \false];
    protected $operandsCount = 0;
    protected $commands = [];
    protected $command;
    protected $additionalOperands = [];
    protected $additionalOptions = [];
    protected static $translator;
    public function __construct($options = null, array $settings = [])
    {
        $this->set(self::SETTING_SCRIPT_NAME, isset($_SERVER['argv'][0]) ? $_SERVER['argv'][0] : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null));
        foreach ($settings as $setting => $value) {
            $this->set($setting, $value);
        }
        if ($options !== null) {
            $this->addOptions($options);
        }
    }
    public function set($setting, $value)
    {
        switch ($setting) {
            case self::SETTING_DEFAULT_MODE:
                OptionParser::$defaultMode = $value;
                break;
            default:
                $this->settings[$setting] = $value;
                break;
        }
        return $this;
    }
    public function get($setting)
    {
        return isset($this->settings[$setting]) ? $this->settings[$setting] : null;
    }
    public function process($arguments = null)
    {
        if ($arguments === null) {
            $arguments = isset($_SERVER['argv']) ? \array_slice($_SERVER['argv'], 1) : [];
            $arguments = new Arguments($arguments);
        } elseif (\is_array($arguments)) {
            $arguments = new Arguments($arguments);
        } elseif (\is_string($arguments)) {
            $arguments = Arguments::fromString($arguments);
        } elseif (!$arguments instanceof Arguments) {
            throw new \InvalidArgumentException('$arguments has to be an instance of Arguments, an arguments string, an array of arguments or null');
        }
        $setOption = function ($name, callable $getValue) {
            $option = $this->getOptionObject($name);
            if (!$option) {
                if (!$this->get(self::SETTING_STRICT_OPTIONS)) {
                    $value = $getValue() ?: 1;
                    if (isset($this->additionalOptions[$name]) && \is_int($value) && \is_int($this->additionalOptions[$name])) {
                        $value += $this->additionalOptions[$name];
                    }
                    $this->additionalOptions[$name] = $value;
                    return;
                } else {
                    throw new Unexpected(\sprintf(self::translate('option-unknown'), $name));
                }
            }
            $option->setValue($option->getMode() !== GetOpt::NO_ARGUMENT ? $getValue($option) : null);
        };
        $setCommand = function (CommandInterface $command) {
            $this->addOptions($command->getOptions());
            $this->addOperands($command->getOperands());
            $this->command = $command;
        };
        $addOperand = function ($value) {
            $operand = $this->nextOperand();
            if ($operand) {
                $operand->setValue($value);
            } elseif ($this->get(self::SETTING_STRICT_OPERANDS)) {
                throw new Unexpected(\sprintf(self::translate('no-more-operands'), $value));
            } else {
                $this->additionalOperands[] = $value;
            }
        };
        $this->additionalOptions = [];
        $this->additionalOperands = [];
        $this->operandsCount = 0;
        $arguments->process($this, $setOption, $setCommand, $addOperand);
        if (($operand = $this->nextOperand()) && $operand->isRequired() && (!$operand->isMultiple() || \count($operand->getValue()) === 0)) {
            throw new Missing(\sprintf(self::translate('operand-missing'), $operand->getName()));
        }
    }
    public function getOption($name, $object = \false)
    {
        $option = $this->getOptionObject($name);
        if ($object) {
            return $option;
        }
        if ($option) {
            return $option->getValue();
        }
        return isset($this->additionalOptions[$name]) ? $this->additionalOptions[$name] : null;
    }
    public function getOptions()
    {
        $result = [];
        foreach ($this->options as $option) {
            $value = $option->getValue();
            if ($value !== null) {
                $result[$option->getShort() ?: $option->getLong()] = $value;
                if ($short = $option->getShort()) {
                    $result[$short] = $value;
                }
                if ($long = $option->getLong()) {
                    $result[$long] = $value;
                }
            }
        }
        return $result + $this->additionalOptions;
    }
    public function addCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }
        return $this;
    }
    public function addCommand(CommandInterface $command)
    {
        foreach ($command->getOptions() as $option) {
            if ($this->conflicts($option)) {
                throw new \InvalidArgumentException('$command has conflicting options');
            }
        }
        $this->commands[$command->getName()] = $command;
        return $this;
    }
    public function getCommand($name = null)
    {
        if ($name !== null) {
            return isset($this->commands[$name]) ? $this->commands[$name] : null;
        }
        return $this->command;
    }
    public function getCommands()
    {
        return $this->commands;
    }
    public function hasCommands()
    {
        return !empty($this->commands);
    }
    protected function nextOperand()
    {
        if (isset($this->operands[$this->operandsCount])) {
            $operand = $this->operands[$this->operandsCount];
            if (!$operand->isMultiple()) {
                $this->operandsCount++;
            }
            return $operand;
        }
        return null;
    }
    public function getOperands()
    {
        $operandValues = [];
        foreach ($this->getOperandObjects() as $operand) {
            $value = $operand->getValue();
            if ($value === null) {
                continue;
            }
            if ($operand->isMultiple()) {
                $operandValues = \array_merge($operandValues, $value);
            } else {
                $operandValues[] = $value;
            }
        }
        return \array_merge($operandValues, $this->additionalOperands);
    }
    public function getOperand($index)
    {
        $operand = $this->getOperandObject($index);
        if ($operand) {
            return $operand->getValue();
        } elseif (\is_int($index)) {
            $i = $index - \count($this->operands);
            return $i >= 0 && isset($this->additionalOperands[$i]) ? $this->additionalOperands[$i] : null;
        }
        return null;
    }
    public function setHelp(HelpInterface $help)
    {
        $this->help = $help;
        return $this;
    }
    public function setHelpLang($language = 'en')
    {
        return self::setLang($language);
    }
    public static function translate($key)
    {
        return self::getTranslator()->translate($key);
    }
    protected static function getTranslator()
    {
        if (self::$translator === null) {
            self::$translator = new Translator();
        }
        return self::$translator;
    }
    public static function setLang($language)
    {
        return self::getTranslator()->setLanguage($language);
    }
    public function getHelp()
    {
        if (!$this->help) {
            $this->help = new Help();
        }
        return $this->help;
    }
    public function getHelpText(array $data = [])
    {
        return $this->getHelp()->render($this, $data);
    }
    public function setScriptName($scriptName)
    {
        return $this->set(self::SETTING_SCRIPT_NAME, $scriptName);
    }
    public function parse($arguments = null)
    {
        $this->process($arguments);
    }
    public function getIterator()
    {
        $result = [];
        foreach ($this->options as $option) {
            if (($value = $option->getValue()) !== null) {
                $name = $option->getLong() ?: $option->getShort();
                $result[$name] = $value;
            }
        }
        return new \ArrayIterator($result + $this->additionalOptions);
    }
    public function offsetExists($offset)
    {
        $option = $this->getOptionObject($offset);
        if ($option && $option->getValue() !== null) {
            return \true;
        }
        return isset($this->additionalOptions[$offset]);
    }
    public function offsetGet($offset)
    {
        $option = $this->getOptionObject($offset);
        if ($option) {
            return $option->getValue();
        }
        return isset($this->additionalOptions[$offset]) ? $this->additionalOptions[$offset] : null;
    }
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Read only array access');
    }
    public function offsetUnset($offset)
    {
        throw new \LogicException('Read only array access');
    }
    public function count()
    {
        return $this->getIterator()->count();
    }
}
