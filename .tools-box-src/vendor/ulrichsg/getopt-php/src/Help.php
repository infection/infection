<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

class Help implements HelpInterface
{
    const TEMPLATE_USAGE = 'usageTemplate';
    const TEMPLATE_OPTIONS = 'optionsTemplate';
    const TEMPLATE_COMMANDS = 'commandsTemplate';
    const DESCRIPTION = 'description';
    const MAX_WIDTH = 'maxWidth';
    const HIDE_OPERANDS = 'hideOperands';
    protected $usageTemplate;
    protected $optionsTemplate;
    protected $commandsTemplate;
    protected $settings = [self::MAX_WIDTH => 120];
    protected $texts = ['placeholder' => '<>', 'optional' => '[]', 'multiple' => '...', 'options-listing' => ', '];
    protected $getOpt;
    protected $screenWidth;
    public function __construct(array $settings = [])
    {
        foreach ($settings as $setting => $value) {
            $this->set($setting, $value);
        }
        $this->setTexts([]);
    }
    public function set($setting, $value)
    {
        switch ($setting) {
            case 'optionsTemplate':
            case 'commandsTemplate':
            case 'usageTemplate':
                \call_user_func([$this, 'set' . \ucfirst($setting)], $value);
                break;
            default:
                $this->settings[$setting] = $value;
                break;
        }
        return $this;
    }
    public function setTexts(array $texts)
    {
        $this->texts = \array_map(function ($text) {
            return \preg_replace('/\\R/', \PHP_EOL, $text);
        }, \array_merge($this->texts, $texts));
        return $this;
    }
    protected function getText($key)
    {
        return isset($this->texts[$key]) ? $this->texts[$key] : GetOpt::translate($key);
    }
    public function render(GetOpt $getopt, array $data = [])
    {
        $this->getOpt = $getopt;
        foreach ($data as $setting => $value) {
            $this->set($setting, $value);
        }
        if ($this->usageTemplate) {
            $data['getopt'] = $getopt;
            $data['command'] = $getopt->getCommand();
            $helpText = $this->renderTemplate($this->usageTemplate, $data);
        } else {
            $helpText = $this->renderUsage();
        }
        if ($getopt->hasOperands() && empty($this->settings[self::HIDE_OPERANDS])) {
            $helpText .= $this->renderOperands();
        }
        if ($getopt->hasOptions()) {
            if ($this->optionsTemplate) {
                $data['options'] = $getopt->getOptionObjects();
                $helpText .= $this->renderTemplate($this->optionsTemplate, $data);
            } else {
                $helpText .= $this->renderOptions();
            }
        }
        if (!$getopt->getCommand() && $getopt->hasCommands()) {
            if ($this->commandsTemplate) {
                $data['commands'] = $getopt->getCommands();
                $helpText .= $this->renderTemplate($this->commandsTemplate, $data);
            } else {
                $helpText .= $this->renderCommands();
            }
        }
        return $helpText;
    }
    protected function surround($text, $with)
    {
        return $with[0] . $text . \substr($with, -1);
    }
    protected function renderUsage()
    {
        return $this->getText('usage-title') . $this->getOpt->get(GetOpt::SETTING_SCRIPT_NAME) . ' ' . $this->renderUsageCommand() . $this->renderUsageOptions() . $this->renderUsageOperands() . \PHP_EOL . \PHP_EOL . $this->renderDescription();
    }
    protected function renderOperands()
    {
        $data = [];
        $definitionWidth = 0;
        $hasDescriptions = \false;
        foreach ($this->getOpt->getOperandObjects() as $operand) {
            $definition = $this->surround($operand->getName(), $this->texts['placeholder']);
            if (!$operand->isRequired()) {
                $definition = $this->surround($definition, $this->texts['optional']);
            }
            if (\strlen($definition) > $definitionWidth) {
                $definitionWidth = \strlen($definition);
            }
            if ($operand->getDescription()) {
                $hasDescriptions = \true;
            }
            $data[] = [$definition, $operand->getDescription()];
        }
        if (!$hasDescriptions) {
            return '';
        }
        return $this->getText('operands-title') . $this->renderColumns($definitionWidth, $data) . \PHP_EOL;
    }
    protected function renderOptions()
    {
        $data = [];
        $definitionWidth = 0;
        foreach ($this->getOpt->getOptionObjects() as $option) {
            $definition = \implode($this->texts['options-listing'], \array_filter([$option->getShort() ? '-' . $option->getShort() : null, $option->getLong() ? '--' . $option->getLong() : null]));
            if ($option->getMode() !== GetOpt::NO_ARGUMENT) {
                $argument = $this->surround($option->getArgument()->getName(), $this->texts['placeholder']);
                if ($option->getMode() === GetOpt::OPTIONAL_ARGUMENT) {
                    $argument = $this->surround($argument, $this->texts['optional']);
                }
                $definition .= ' ' . $argument;
            }
            if (\strlen($definition) > $definitionWidth) {
                $definitionWidth = \strlen($definition);
            }
            $data[] = [$definition, $option->getDescription()];
        }
        return $this->getText('options-title') . $this->renderColumns($definitionWidth, $data) . \PHP_EOL;
    }
    protected function renderCommands()
    {
        $data = [];
        $nameWidth = 0;
        foreach ($this->getOpt->getCommands() as $command) {
            if (\strlen($command->getName()) > $nameWidth) {
                $nameWidth = \strlen($command->getName());
            }
            $data[] = [$command->getName(), $command->getShortDescription()];
        }
        return $this->getText('commands-title') . $this->renderColumns($nameWidth, $data) . \PHP_EOL;
    }
    protected function renderUsageCommand()
    {
        if ($command = $this->getOpt->getCommand()) {
            return $command->getName() . ' ';
        } elseif ($this->getOpt->hasCommands()) {
            return $this->surround($this->getText('usage-command'), $this->texts['placeholder']) . ' ';
        }
        return '';
    }
    protected function renderUsageOptions()
    {
        if ($this->getOpt->hasOptions() || !$this->getOpt->get(GetOpt::SETTING_STRICT_OPTIONS)) {
            return $this->surround($this->getText('usage-options'), $this->texts['optional']) . ' ';
        }
    }
    protected function renderUsageOperands()
    {
        $usage = '';
        $lastOperandMultiple = \false;
        if ($this->getOpt->hasOperands()) {
            foreach ($this->getOpt->getOperandObjects() as $operand) {
                $name = $this->surround($operand->getName(), $this->texts['placeholder']);
                if (!$operand->isRequired()) {
                    $name = $this->surround($name, $this->texts['optional']);
                }
                $usage .= $name . ' ';
                if ($operand->isMultiple()) {
                    $usage .= $this->surround($this->surround($operand->getName(), $this->texts['placeholder']) . $this->texts['multiple'], $this->texts['optional']);
                    $lastOperandMultiple = \true;
                }
            }
        }
        if (!$lastOperandMultiple && !$this->getOpt->get(GetOpt::SETTING_STRICT_OPERANDS)) {
            $usage .= $this->surround($this->getText('usage-operands'), $this->texts['optional']);
        }
        return $usage;
    }
    protected function renderDescription()
    {
        if ($command = $this->getOpt->getCommand()) {
            return $command->getDescription() . \PHP_EOL . \PHP_EOL;
        } elseif (isset($this->settings[self::DESCRIPTION])) {
            return $this->settings[self::DESCRIPTION] . \PHP_EOL . \PHP_EOL;
        }
        return '';
    }
    protected function getScreenWidth()
    {
        if (!$this->screenWidth) {
            $columns = \defined('COLUMNS') ? (int) COLUMNS : (int) @\getenv('COLUMNS');
            if (empty($columns)) {
                $process = \proc_open('tput cols', [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
                $columns = (int) \stream_get_contents($pipes[1]);
                \proc_close($process);
            }
            $screenWidth = !empty($columns) ? $columns : 90;
            $this->screenWidth = \min([$this->settings[self::MAX_WIDTH], $screenWidth]);
        }
        return $this->screenWidth;
    }
    protected function renderColumns($columnWidth, $data)
    {
        $text = '';
        $screenWidth = $this->getScreenWidth();
        foreach ($data as $dataRow) {
            $row = \sprintf('  % -' . $columnWidth . 's  %s', $dataRow[0], $dataRow[1]);
            while (\mb_strlen($row) > $screenWidth) {
                $p = \strrpos(\substr($row, 0, $screenWidth), ' ');
                if ($p < $columnWidth + 4) {
                    $p = \strrpos(\substr($row, 0, $screenWidth), '-');
                    if ($p < $columnWidth + 4) {
                        $p = $screenWidth - 1;
                    }
                }
                $c = \substr($row, $p, 1);
                $text .= \substr($row, 0, $p) . ($c !== ' ' ? $c : '') . \PHP_EOL;
                $row = \sprintf('  %s  %s', \str_repeat(' ', $columnWidth), \substr($row, $p + 1));
            }
            $text .= $row . \PHP_EOL;
        }
        return $text;
    }
    protected function renderTemplate($template, $data)
    {
        \extract($data, \EXTR_SKIP);
        \ob_start();
        include $template;
        return \ob_get_clean();
    }
    public function getUsageTemplate()
    {
        return $this->usageTemplate;
    }
    public function setUsageTemplate($usageTemplate)
    {
        $this->usageTemplate = $usageTemplate;
        return $this;
    }
    public function getOptionsTemplate()
    {
        return $this->optionsTemplate;
    }
    public function setOptionsTemplate($optionsTemplate)
    {
        $this->optionsTemplate = $optionsTemplate;
        return $this;
    }
    public function getCommandsTemplate()
    {
        return $this->commandsTemplate;
    }
    public function setCommandsTemplate($commandsTemplate)
    {
        $this->commandsTemplate = $commandsTemplate;
        return $this;
    }
}
