<?php

namespace Parle;

use JetBrains\PhpStorm\Immutable;

class RParser
{

public const ACTION_ERROR = 0;
public const ACTION_SHIFT = 1;
public const ACTION_REDUCE = 2;
public const ACTION_GOTO = 3;
public const ACTION_ACCEPT = 4;
public const ERROR_SYNTAX = 0;
public const ERROR_NON_ASSOCIATIVE = 1;
public const ERROR_UNKNOWN_TOKEN = 2;





#[Immutable]
public $action = 0;




#[Immutable]
public $reduceId = 0;








public function advance(): void {}










public function build(): void {}









public function consume(string $data, Lexer $lexer): void {}







public function dump(): void {}







public function errorInfo(): ErrorInfo {}








public function left(string $token): void {}










public function nonassoc(string $token): void {}











public function precedence(string $token): void {}











public function push(string $name, string $rule): int {}








public function reset(int $tokenId): void {}








public function right(string $token): void {}











public function sigil(int $idx): string {}










public function token(string $token): void {}











public function tokenId(string $token): int {}










public function trace(): string {}











public function validate(string $data, RLexer $lexer): bool {}
}
