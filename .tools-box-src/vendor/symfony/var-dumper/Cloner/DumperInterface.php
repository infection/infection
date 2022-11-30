<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner;

interface DumperInterface
{
    public function dumpScalar(Cursor $cursor, string $type, string|int|float|bool|null $value);
    public function dumpString(Cursor $cursor, string $str, bool $bin, int $cut);
    public function enterHash(Cursor $cursor, int $type, string|int|null $class, bool $hasChild);
    public function leaveHash(Cursor $cursor, int $type, string|int|null $class, bool $hasChild, int $cut);
}
