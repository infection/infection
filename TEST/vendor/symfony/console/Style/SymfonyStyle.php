<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Style;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\RuntimeException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatter;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\Helper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\ProgressBar;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\Table;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\TableCell;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\TableSeparator;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\ConsoleOutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\TrimmedBufferOutput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\ChoiceQuestion;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\ConfirmationQuestion;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\Question;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Terminal;
class SymfonyStyle extends OutputStyle
{
    public const MAX_LINE_LENGTH = 120;
    private $input;
    private $output;
    private $questionHelper;
    private $progressBar;
    private $lineLength;
    private $bufferedOutput;
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->bufferedOutput = new TrimmedBufferOutput(\DIRECTORY_SEPARATOR === '\\' ? 4 : 2, $output->getVerbosity(), \false, clone $output->getFormatter());
        $width = (new Terminal())->getWidth() ?: self::MAX_LINE_LENGTH;
        $this->lineLength = \min($width - (int) (\DIRECTORY_SEPARATOR === '\\'), self::MAX_LINE_LENGTH);
        parent::__construct($this->output = $output);
    }
    public function block($messages, string $type = null, string $style = null, string $prefix = ' ', bool $padding = \false, bool $escape = \true)
    {
        $messages = \is_array($messages) ? \array_values($messages) : [$messages];
        $this->autoPrependBlock();
        $this->writeln($this->createBlock($messages, $type, $style, $prefix, $padding, $escape));
        $this->newLine();
    }
    public function title(string $message)
    {
        $this->autoPrependBlock();
        $this->writeln([\sprintf('<comment>%s</>', OutputFormatter::escapeTrailingBackslash($message)), \sprintf('<comment>%s</>', \str_repeat('=', Helper::width(Helper::removeDecoration($this->getFormatter(), $message))))]);
        $this->newLine();
    }
    public function section(string $message)
    {
        $this->autoPrependBlock();
        $this->writeln([\sprintf('<comment>%s</>', OutputFormatter::escapeTrailingBackslash($message)), \sprintf('<comment>%s</>', \str_repeat('-', Helper::width(Helper::removeDecoration($this->getFormatter(), $message))))]);
        $this->newLine();
    }
    public function listing(array $elements)
    {
        $this->autoPrependText();
        $elements = \array_map(function ($element) {
            return \sprintf(' * %s', $element);
        }, $elements);
        $this->writeln($elements);
        $this->newLine();
    }
    public function text($message)
    {
        $this->autoPrependText();
        $messages = \is_array($message) ? \array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->writeln(\sprintf(' %s', $message));
        }
    }
    public function comment($message)
    {
        $this->block($message, null, null, '<fg=default;bg=default> // </>', \false, \false);
    }
    public function success($message)
    {
        $this->block($message, 'OK', 'fg=black;bg=green', ' ', \true);
    }
    public function error($message)
    {
        $this->block($message, 'ERROR', 'fg=white;bg=red', ' ', \true);
    }
    public function warning($message)
    {
        $this->block($message, 'WARNING', 'fg=black;bg=yellow', ' ', \true);
    }
    public function note($message)
    {
        $this->block($message, 'NOTE', 'fg=yellow', ' ! ');
    }
    public function info($message)
    {
        $this->block($message, 'INFO', 'fg=green', ' ', \true);
    }
    public function caution($message)
    {
        $this->block($message, 'CAUTION', 'fg=white;bg=red', ' ! ', \true);
    }
    public function table(array $headers, array $rows)
    {
        $this->createTable()->setHeaders($headers)->setRows($rows)->render();
        $this->newLine();
    }
    public function horizontalTable(array $headers, array $rows)
    {
        $this->createTable()->setHorizontal(\true)->setHeaders($headers)->setRows($rows)->render();
        $this->newLine();
    }
    public function definitionList(...$list)
    {
        $headers = [];
        $row = [];
        foreach ($list as $value) {
            if ($value instanceof TableSeparator) {
                $headers[] = $value;
                $row[] = $value;
                continue;
            }
            if (\is_string($value)) {
                $headers[] = new TableCell($value, ['colspan' => 2]);
                $row[] = null;
                continue;
            }
            if (!\is_array($value)) {
                throw new InvalidArgumentException('Value should be an array, string, or an instance of TableSeparator.');
            }
            $headers[] = \key($value);
            $row[] = \current($value);
        }
        $this->horizontalTable($headers, [$row]);
    }
    public function ask(string $question, string $default = null, callable $validator = null)
    {
        $question = new Question($question, $default);
        $question->setValidator($validator);
        return $this->askQuestion($question);
    }
    public function askHidden(string $question, callable $validator = null)
    {
        $question = new Question($question);
        $question->setHidden(\true);
        $question->setValidator($validator);
        return $this->askQuestion($question);
    }
    public function confirm(string $question, bool $default = \true)
    {
        return $this->askQuestion(new ConfirmationQuestion($question, $default));
    }
    public function choice(string $question, array $choices, $default = null)
    {
        if (null !== $default) {
            $values = \array_flip($choices);
            $default = $values[$default] ?? $default;
        }
        return $this->askQuestion(new ChoiceQuestion($question, $choices, $default));
    }
    public function progressStart(int $max = 0)
    {
        $this->progressBar = $this->createProgressBar($max);
        $this->progressBar->start();
    }
    public function progressAdvance(int $step = 1)
    {
        $this->getProgressBar()->advance($step);
    }
    public function progressFinish()
    {
        $this->getProgressBar()->finish();
        $this->newLine(2);
        $this->progressBar = null;
    }
    public function createProgressBar(int $max = 0)
    {
        $progressBar = parent::createProgressBar($max);
        if ('\\' !== \DIRECTORY_SEPARATOR || 'Hyper' === \getenv('TERM_PROGRAM')) {
            $progressBar->setEmptyBarCharacter('░');
            $progressBar->setProgressCharacter('');
            $progressBar->setBarCharacter('▓');
        }
        return $progressBar;
    }
    public function progressIterate(iterable $iterable, int $max = null) : iterable
    {
        yield from $this->createProgressBar()->iterate($iterable, $max);
        $this->newLine(2);
    }
    public function askQuestion(Question $question)
    {
        if ($this->input->isInteractive()) {
            $this->autoPrependBlock();
        }
        if (!$this->questionHelper) {
            $this->questionHelper = new SymfonyQuestionHelper();
        }
        $answer = $this->questionHelper->ask($this->input, $this, $question);
        if ($this->input->isInteractive()) {
            $this->newLine();
            $this->bufferedOutput->write("\n");
        }
        return $answer;
    }
    public function writeln($messages, int $type = self::OUTPUT_NORMAL)
    {
        if (!\is_iterable($messages)) {
            $messages = [$messages];
        }
        foreach ($messages as $message) {
            parent::writeln($message, $type);
            $this->writeBuffer($message, \true, $type);
        }
    }
    public function write($messages, bool $newline = \false, int $type = self::OUTPUT_NORMAL)
    {
        if (!\is_iterable($messages)) {
            $messages = [$messages];
        }
        foreach ($messages as $message) {
            parent::write($message, $newline, $type);
            $this->writeBuffer($message, $newline, $type);
        }
    }
    public function newLine(int $count = 1)
    {
        parent::newLine($count);
        $this->bufferedOutput->write(\str_repeat("\n", $count));
    }
    public function getErrorStyle()
    {
        return new self($this->input, $this->getErrorOutput());
    }
    public function createTable() : Table
    {
        $output = $this->output instanceof ConsoleOutputInterface ? $this->output->section() : $this->output;
        $style = clone Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');
        return (new Table($output))->setStyle($style);
    }
    private function getProgressBar() : ProgressBar
    {
        if (!$this->progressBar) {
            throw new RuntimeException('The ProgressBar is not started.');
        }
        return $this->progressBar;
    }
    private function autoPrependBlock() : void
    {
        $chars = \substr(\str_replace(\PHP_EOL, "\n", $this->bufferedOutput->fetch()), -2);
        if (!isset($chars[0])) {
            $this->newLine();
            return;
        }
        $this->newLine(2 - \substr_count($chars, "\n"));
    }
    private function autoPrependText() : void
    {
        $fetched = $this->bufferedOutput->fetch();
        if (!\str_ends_with($fetched, "\n")) {
            $this->newLine();
        }
    }
    private function writeBuffer(string $message, bool $newLine, int $type) : void
    {
        $this->bufferedOutput->write($message, $newLine, $type);
    }
    private function createBlock(iterable $messages, string $type = null, string $style = null, string $prefix = ' ', bool $padding = \false, bool $escape = \false) : array
    {
        $indentLength = 0;
        $prefixLength = Helper::width(Helper::removeDecoration($this->getFormatter(), $prefix));
        $lines = [];
        if (null !== $type) {
            $type = \sprintf('[%s] ', $type);
            $indentLength = \strlen($type);
            $lineIndentation = \str_repeat(' ', $indentLength);
        }
        foreach ($messages as $key => $message) {
            if ($escape) {
                $message = OutputFormatter::escape($message);
            }
            $decorationLength = Helper::width($message) - Helper::width(Helper::removeDecoration($this->getFormatter(), $message));
            $messageLineLength = \min($this->lineLength - $prefixLength - $indentLength + $decorationLength, $this->lineLength);
            $messageLines = \explode(\PHP_EOL, \wordwrap($message, $messageLineLength, \PHP_EOL, \true));
            foreach ($messageLines as $messageLine) {
                $lines[] = $messageLine;
            }
            if (\count($messages) > 1 && $key < \count($messages) - 1) {
                $lines[] = '';
            }
        }
        $firstLineIndex = 0;
        if ($padding && $this->isDecorated()) {
            $firstLineIndex = 1;
            \array_unshift($lines, '');
            $lines[] = '';
        }
        foreach ($lines as $i => &$line) {
            if (null !== $type) {
                $line = $firstLineIndex === $i ? $type . $line : $lineIndentation . $line;
            }
            $line = $prefix . $line;
            $line .= \str_repeat(' ', \max($this->lineLength - Helper::width(Helper::removeDecoration($this->getFormatter(), $line)), 0));
            if ($style) {
                $line = \sprintf('<%s>%s</>', $style, $line);
            }
        }
        return $lines;
    }
}
