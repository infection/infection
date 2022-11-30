<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatter;
class FormatterHelper extends Helper
{
    public function formatSection(string $section, string $message, string $style = 'info')
    {
        return \sprintf('<%s>[%s]</%s> %s', $style, $section, $style, $message);
    }
    public function formatBlock($messages, string $style, bool $large = \false)
    {
        if (!\is_array($messages)) {
            $messages = [$messages];
        }
        $len = 0;
        $lines = [];
        foreach ($messages as $message) {
            $message = OutputFormatter::escape($message);
            $lines[] = \sprintf($large ? '  %s  ' : ' %s ', $message);
            $len = \max(self::width($message) + ($large ? 4 : 2), $len);
        }
        $messages = $large ? [\str_repeat(' ', $len)] : [];
        for ($i = 0; isset($lines[$i]); ++$i) {
            $messages[] = $lines[$i] . \str_repeat(' ', $len - self::width($lines[$i]));
        }
        if ($large) {
            $messages[] = \str_repeat(' ', $len);
        }
        for ($i = 0; isset($messages[$i]); ++$i) {
            $messages[$i] = \sprintf('<%s>%s</%s>', $style, $messages[$i], $style);
        }
        return \implode("\n", $messages);
    }
    public function truncate(string $message, int $length, string $suffix = '...')
    {
        $computedLength = $length - self::width($suffix);
        if ($computedLength > self::width($message)) {
            return $message;
        }
        return self::substr($message, 0, $length) . $suffix;
    }
    public function getName()
    {
        return 'formatter';
    }
}
