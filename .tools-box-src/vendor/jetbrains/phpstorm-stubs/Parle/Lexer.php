<?php

namespace Parle;

use JetBrains\PhpStorm\Immutable;












class Lexer
{

public const ICASE = 1;
public const DOT_NOT_LF = 2;
public const DOT_NOT_CRLF = 4;
public const SKIP_WS = 8;
public const MATCH_ZERO_LEN = 16;





public $bol = false;




public $flags = 0;




#[Immutable]
public $state = 0;




#[Immutable]
public $marker = 0;




#[Immutable]
public $cursor = 0;









public function advance(): void {}












public function build(): void {}











public function callout(int $id, callable $callback): void {}








public function consume(string $data): void {}







public function dump(): void {}






public function getToken(): Token {}









public function insertMacro(string $name, string $regex): void {}








public function push(string $regex, int $id): void {}






public function reset(int $pos): void {}
}
