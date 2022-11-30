<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Style;

interface StyleInterface
{
    public function title(string $message);
    public function section(string $message);
    public function listing(array $elements);
    public function text($message);
    public function success($message);
    public function error($message);
    public function warning($message);
    public function note($message);
    public function caution($message);
    public function table(array $headers, array $rows);
    public function ask(string $question, string $default = null, callable $validator = null);
    public function askHidden(string $question, callable $validator = null);
    public function confirm(string $question, bool $default = \true);
    public function choice(string $question, array $choices, $default = null);
    public function newLine(int $count = 1);
    public function progressStart(int $max = 0);
    public function progressAdvance(int $step = 1);
    public function progressFinish();
}
