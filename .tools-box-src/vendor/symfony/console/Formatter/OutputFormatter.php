<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
class OutputFormatter implements WrappableOutputFormatterInterface
{
    private bool $decorated;
    private array $styles = [];
    private OutputFormatterStyleStack $styleStack;
    public function __clone()
    {
        $this->styleStack = clone $this->styleStack;
        foreach ($this->styles as $key => $value) {
            $this->styles[$key] = clone $value;
        }
    }
    public static function escape(string $text) : string
    {
        $text = \preg_replace('/([^\\\\]|^)([<>])/', '$1\\\\$2', $text);
        return self::escapeTrailingBackslash($text);
    }
    public static function escapeTrailingBackslash(string $text) : string
    {
        if (\str_ends_with($text, '\\')) {
            $len = \strlen($text);
            $text = \rtrim($text, '\\');
            $text = \str_replace("\x00", '', $text);
            $text .= \str_repeat("\x00", $len - \strlen($text));
        }
        return $text;
    }
    public function __construct(bool $decorated = \false, array $styles = [])
    {
        $this->decorated = $decorated;
        $this->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $this->setStyle('info', new OutputFormatterStyle('green'));
        $this->setStyle('comment', new OutputFormatterStyle('yellow'));
        $this->setStyle('question', new OutputFormatterStyle('black', 'cyan'));
        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }
        $this->styleStack = new OutputFormatterStyleStack();
    }
    public function setDecorated(bool $decorated)
    {
        $this->decorated = $decorated;
    }
    public function isDecorated() : bool
    {
        return $this->decorated;
    }
    public function setStyle(string $name, OutputFormatterStyleInterface $style)
    {
        $this->styles[\strtolower($name)] = $style;
    }
    public function hasStyle(string $name) : bool
    {
        return isset($this->styles[\strtolower($name)]);
    }
    public function getStyle(string $name) : OutputFormatterStyleInterface
    {
        if (!$this->hasStyle($name)) {
            throw new InvalidArgumentException(\sprintf('Undefined style: "%s".', $name));
        }
        return $this->styles[\strtolower($name)];
    }
    public function format(?string $message) : ?string
    {
        return $this->formatAndWrap($message, 0);
    }
    public function formatAndWrap(?string $message, int $width)
    {
        if (null === $message) {
            return '';
        }
        $offset = 0;
        $output = '';
        $openTagRegex = '[a-z](?:[^\\\\<>]*+ | \\\\.)*';
        $closeTagRegex = '[a-z][^<>]*+';
        $currentLineLength = 0;
        \preg_match_all("#<(({$openTagRegex}) | /({$closeTagRegex})?)>#ix", $message, $matches, \PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $i => $match) {
            $pos = $match[1];
            $text = $match[0];
            if (0 != $pos && '\\' == $message[$pos - 1]) {
                continue;
            }
            $output .= $this->applyCurrentStyle(\substr($message, $offset, $pos - $offset), $output, $width, $currentLineLength);
            $offset = $pos + \strlen($text);
            if ($open = '/' != $text[1]) {
                $tag = $matches[1][$i][0];
            } else {
                $tag = $matches[3][$i][0] ?? '';
            }
            if (!$open && !$tag) {
                $this->styleStack->pop();
            } elseif (null === ($style = $this->createStyleFromString($tag))) {
                $output .= $this->applyCurrentStyle($text, $output, $width, $currentLineLength);
            } elseif ($open) {
                $this->styleStack->push($style);
            } else {
                $this->styleStack->pop($style);
            }
        }
        $output .= $this->applyCurrentStyle(\substr($message, $offset), $output, $width, $currentLineLength);
        return \strtr($output, ["\x00" => '\\', '\\<' => '<', '\\>' => '>']);
    }
    public function getStyleStack() : OutputFormatterStyleStack
    {
        return $this->styleStack;
    }
    private function createStyleFromString(string $string) : ?OutputFormatterStyleInterface
    {
        if (isset($this->styles[$string])) {
            return $this->styles[$string];
        }
        if (!\preg_match_all('/([^=]+)=([^;]+)(;|$)/', $string, $matches, \PREG_SET_ORDER)) {
            return null;
        }
        $style = new OutputFormatterStyle();
        foreach ($matches as $match) {
            \array_shift($match);
            $match[0] = \strtolower($match[0]);
            if ('fg' == $match[0]) {
                $style->setForeground(\strtolower($match[1]));
            } elseif ('bg' == $match[0]) {
                $style->setBackground(\strtolower($match[1]));
            } elseif ('href' === $match[0]) {
                $url = \preg_replace('{\\\\([<>])}', '$1', $match[1]);
                $style->setHref($url);
            } elseif ('options' === $match[0]) {
                \preg_match_all('([^,;]+)', \strtolower($match[1]), $options);
                $options = \array_shift($options);
                foreach ($options as $option) {
                    $style->setOption($option);
                }
            } else {
                return null;
            }
        }
        return $style;
    }
    private function applyCurrentStyle(string $text, string $current, int $width, int &$currentLineLength) : string
    {
        if ('' === $text) {
            return '';
        }
        if (!$width) {
            return $this->isDecorated() ? $this->styleStack->getCurrent()->apply($text) : $text;
        }
        if (!$currentLineLength && '' !== $current) {
            $text = \ltrim($text);
        }
        if ($currentLineLength) {
            $prefix = \substr($text, 0, $i = $width - $currentLineLength) . "\n";
            $text = \substr($text, $i);
        } else {
            $prefix = '';
        }
        \preg_match('~(\\n)$~', $text, $matches);
        $text = $prefix . \preg_replace('~([^\\n]{' . $width . '})\\ *~', "\$1\n", $text);
        $text = \rtrim($text, "\n") . ($matches[1] ?? '');
        if (!$currentLineLength && '' !== $current && !\str_ends_with($current, "\n")) {
            $text = "\n" . $text;
        }
        $lines = \explode("\n", $text);
        foreach ($lines as $line) {
            $currentLineLength += \strlen($line);
            if ($width <= $currentLineLength) {
                $currentLineLength = 0;
            }
        }
        if ($this->isDecorated()) {
            foreach ($lines as $i => $line) {
                $lines[$i] = $this->styleStack->getCurrent()->apply($line);
            }
        }
        return \implode("\n", $lines);
    }
}
