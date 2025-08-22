<?php

declare(strict_types=1);

namespace newSrc\AST\AridCodeDetector;

use PhpParser\Node;

final class LogStatementDetector implements AridCodeDetector
{
    private const PHP_NATIVE_LOG_FUNCTION = 'error_log';

    public function isArid(Node $node): bool
    {
        // TODO: this is not all implemented!
        return match(true) {
            $node instanceof Node\Expr\FuncCall => $this->isLogLikeFunctionCall($node),
            $node instanceof Node\Expr\MethodCall => $this->isLogLikeMethodCall($node),
            default => false,
        };
    }

    private function isLogLikeFunctionCall(Node\Expr\FuncCall $node): bool
    {
        $name = $node->name;

        return $name instanceof Node\Name
            && $name->toLowerString() === self::PHP_NATIVE_LOG_FUNCTION;
    }

    private function isLogLikeMethodCall(Node\Expr\MethodCall $node): bool
    {
        $var = $node->var;
        $name = $node->name;

        return
            $var instanceof Node\Expr\PropertyFetch
            && $var->name instanceof Node\Identifier
            && 'logger' === $var->name->toLowerString();
    }
}
