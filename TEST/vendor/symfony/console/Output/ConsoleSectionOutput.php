<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Output;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatterInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\Helper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Terminal;
class ConsoleSectionOutput extends StreamOutput
{
    private $content = [];
    private $lines = 0;
    private $sections;
    private $terminal;
    public function __construct($stream, array &$sections, int $verbosity, bool $decorated, OutputFormatterInterface $formatter)
    {
        parent::__construct($stream, $verbosity, $decorated, $formatter);
        \array_unshift($sections, $this);
        $this->sections =& $sections;
        $this->terminal = new Terminal();
    }
    public function clear(int $lines = null)
    {
        if (empty($this->content) || !$this->isDecorated()) {
            return;
        }
        if ($lines) {
            \array_splice($this->content, -($lines * 2));
        } else {
            $lines = $this->lines;
            $this->content = [];
        }
        $this->lines -= $lines;
        parent::doWrite($this->popStreamContentUntilCurrentSection($lines), \false);
    }
    public function overwrite($message)
    {
        $this->clear();
        $this->writeln($message);
    }
    public function getContent() : string
    {
        return \implode('', $this->content);
    }
    public function addContent(string $input)
    {
        foreach (\explode(\PHP_EOL, $input) as $lineContent) {
            $this->lines += \ceil($this->getDisplayLength($lineContent) / $this->terminal->getWidth()) ?: 1;
            $this->content[] = $lineContent;
            $this->content[] = \PHP_EOL;
        }
    }
    protected function doWrite(string $message, bool $newline)
    {
        if (!$this->isDecorated()) {
            parent::doWrite($message, $newline);
            return;
        }
        $erasedContent = $this->popStreamContentUntilCurrentSection();
        $this->addContent($message);
        parent::doWrite($message, \true);
        parent::doWrite($erasedContent, \false);
    }
    private function popStreamContentUntilCurrentSection(int $numberOfLinesToClearFromCurrentSection = 0) : string
    {
        $numberOfLinesToClear = $numberOfLinesToClearFromCurrentSection;
        $erasedContent = [];
        foreach ($this->sections as $section) {
            if ($section === $this) {
                break;
            }
            $numberOfLinesToClear += $section->lines;
            $erasedContent[] = $section->getContent();
        }
        if ($numberOfLinesToClear > 0) {
            parent::doWrite(\sprintf("\x1b[%dA", $numberOfLinesToClear), \false);
            parent::doWrite("\x1b[0J", \false);
        }
        return \implode('', \array_reverse($erasedContent));
    }
    private function getDisplayLength(string $text) : int
    {
        return Helper::width(Helper::removeDecoration($this->getFormatter(), \str_replace("\t", '        ', $text)));
    }
}
