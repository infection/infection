<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\StringNodePrefixer;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\String_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
use function ltrim;
use function str_starts_with;
use function substr;
final class NewdocPrefixer extends NodeVisitorAbstract
{
    public function __construct(private readonly StringNodePrefixer $stringPrefixer)
    {
    }
    public function enterNode(Node $node) : Node
    {
        if ($node instanceof String_ && $this->isPhpNowdoc($node)) {
            $this->stringPrefixer->prefixStringValue($node);
        }
        return $node;
    }
    private function isPhpNowdoc(String_ $node) : bool
    {
        if (String_::KIND_NOWDOC !== $node->getAttribute('kind')) {
            return \false;
        }
        return str_starts_with(substr(ltrim($node->value), 0, 5), '<?php');
    }
}
