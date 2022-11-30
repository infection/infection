<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Dumper;

use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Data;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\DumperInterface;
abstract class AbstractDumper implements DataDumperInterface, DumperInterface
{
    public const DUMP_LIGHT_ARRAY = 1;
    public const DUMP_STRING_LENGTH = 2;
    public const DUMP_COMMA_SEPARATOR = 4;
    public const DUMP_TRAILING_COMMA = 8;
    public static $defaultOutput = 'php://output';
    protected $line = '';
    protected $lineDumper;
    protected $outputStream;
    protected $decimalPoint = '.';
    protected $indentPad = '  ';
    protected $flags;
    private string $charset = '';
    public function __construct($output = null, string $charset = null, int $flags = 0)
    {
        $this->flags = $flags;
        $this->setCharset((($charset ?: \ini_get('php.output_encoding')) ?: \ini_get('default_charset')) ?: 'UTF-8');
        $this->setOutput($output ?: static::$defaultOutput);
        if (!$output && \is_string(static::$defaultOutput)) {
            static::$defaultOutput = $this->outputStream;
        }
    }
    public function setOutput($output)
    {
        $prev = $this->outputStream ?? $this->lineDumper;
        if (\is_callable($output)) {
            $this->outputStream = null;
            $this->lineDumper = $output;
        } else {
            if (\is_string($output)) {
                $output = \fopen($output, 'w');
            }
            $this->outputStream = $output;
            $this->lineDumper = $this->echoLine(...);
        }
        return $prev;
    }
    public function setCharset(string $charset) : string
    {
        $prev = $this->charset;
        $charset = \strtoupper($charset);
        $charset = null === $charset || 'UTF-8' === $charset || 'UTF8' === $charset ? 'CP1252' : $charset;
        $this->charset = $charset;
        return $prev;
    }
    public function setIndentPad(string $pad) : string
    {
        $prev = $this->indentPad;
        $this->indentPad = $pad;
        return $prev;
    }
    public function dump(Data $data, $output = null) : ?string
    {
        if ($locale = $this->flags & (self::DUMP_COMMA_SEPARATOR | self::DUMP_TRAILING_COMMA) ? \setlocale(\LC_NUMERIC, 0) : null) {
            \setlocale(\LC_NUMERIC, 'C');
        }
        if ($returnDump = \true === $output) {
            $output = \fopen('php://memory', 'r+');
        }
        if ($output) {
            $prevOutput = $this->setOutput($output);
        }
        try {
            $data->dump($this);
            $this->dumpLine(-1);
            if ($returnDump) {
                $result = \stream_get_contents($output, -1, 0);
                \fclose($output);
                return $result;
            }
        } finally {
            if ($output) {
                $this->setOutput($prevOutput);
            }
            if ($locale) {
                \setlocale(\LC_NUMERIC, $locale);
            }
        }
        return null;
    }
    protected function dumpLine(int $depth)
    {
        ($this->lineDumper)($this->line, $depth, $this->indentPad);
        $this->line = '';
    }
    protected function echoLine(string $line, int $depth, string $indentPad)
    {
        if (-1 !== $depth) {
            \fwrite($this->outputStream, \str_repeat($indentPad, $depth) . $line . "\n");
        }
    }
    protected function utf8Encode(?string $s) : ?string
    {
        if (null === $s || \preg_match('//u', $s)) {
            return $s;
        }
        if (!\function_exists('iconv')) {
            throw new \RuntimeException('Unable to convert a non-UTF-8 string to UTF-8: required function iconv() does not exist. You should install ext-iconv or symfony/polyfill-iconv.');
        }
        if (\false !== ($c = @\iconv($this->charset, 'UTF-8', $s))) {
            return $c;
        }
        if ('CP1252' !== $this->charset && \false !== ($c = @\iconv('CP1252', 'UTF-8', $s))) {
            return $c;
        }
        return \iconv('CP850', 'UTF-8', $s);
    }
}
