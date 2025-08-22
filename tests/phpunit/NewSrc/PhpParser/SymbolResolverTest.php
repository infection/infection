<?php

namespace Infection\Tests\NewSrc\PhpParser;

use newSrc\AST\SymbolResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SymbolResolver::class)]
final class SymbolResolverTest extends TestCase
{
    public function test_it_can_resolve_a_symbol(): void
    {

    }

    public static function symbolProvider(): iterable
    {
        // We only provide minimal tests here. A more complete suite is provided with its
        // corresponding visitor.
        yield [];
    }
}
