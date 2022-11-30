<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Style;

interface StyleInterface
{
    public function title(string $message);
    public function section(string $message);
    public function listing(array $elements);
    public function text(string|array $message);
    public function success(string|array $message);
    public function error(string|array $message);
    public function warning(string|array $message);
    public function note(string|array $message);
    public function caution(string|array $message);
    public function table(array $headers, array $rows);
    public function ask(string $question, string $default = null, callable $validator = null) : mixed;
    public function askHidden(string $question, callable $validator = null) : mixed;
    public function confirm(string $question, bool $default = \true) : bool;
    public function choice(string $question, array $choices, mixed $default = null) : mixed;
    public function newLine(int $count = 1);
    public function progressStart(int $max = 0);
    public function progressAdvance(int $step = 1);
    public function progressFinish();
}
