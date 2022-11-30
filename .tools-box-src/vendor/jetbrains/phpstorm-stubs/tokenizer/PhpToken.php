<?php







class PhpToken implements Stringable
{




public int $id;




public string $text;




public int $line;




public int $pos;







final public function __construct(int $id, string $text, int $line = -1, int $pos = -1) {}






public function getTokenName(): ?string {}









public static function tokenize(string $code, int $flags = 0): array {}








public function is($kind): bool {}






public function isIgnorable(): bool {}




public function __toString(): string {}
}
