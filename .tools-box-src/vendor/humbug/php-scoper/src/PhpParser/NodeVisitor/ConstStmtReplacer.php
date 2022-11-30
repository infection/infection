<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\IdentifierResolver;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Arg;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\FuncCall;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\String_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Const_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Expression;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
use UnexpectedValueException;
use function count;
final class ConstStmtReplacer extends NodeVisitorAbstract
{
    public function __construct(private readonly IdentifierResolver $identifierResolver, private readonly EnrichedReflector $enrichedReflector)
    {
    }
    public function enterNode(Node $node) : Node
    {
        if (!$node instanceof Const_) {
            return $node;
        }
        foreach ($node->consts as $constant) {
            $replacement = $this->replaceConst($node, $constant);
            if (null !== $replacement) {
                return $replacement;
            }
        }
        return $node;
    }
    private function replaceConst(Const_ $const, Node\Const_ $constant) : ?Node
    {
        $resolvedConstantName = $this->identifierResolver->resolveIdentifier($constant->name);
        if (!$this->enrichedReflector->isExposedConstant((string) $resolvedConstantName)) {
            return null;
        }
        if (count($const->consts) > 1) {
            throw new UnexpectedValueException('Exposing a constant declared in a grouped constant statement (e.g. `const FOO = \'foo\', BAR = \'bar\'; is not supported. Consider breaking it down in multiple constant declaration statements');
        }
        return self::createConstDefineNode((string) $resolvedConstantName, $constant->value);
    }
    private static function createConstDefineNode(string $name, Expr $value) : Node
    {
        return new Expression(new FuncCall(new FullyQualified('define'), [new Arg(new String_($name)), new Arg($value)]));
    }
}
