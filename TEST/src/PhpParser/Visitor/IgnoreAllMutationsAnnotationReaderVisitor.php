<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitorAbstract;
use SplObjectStorage;
use function str_contains;
final class IgnoreAllMutationsAnnotationReaderVisitor extends NodeVisitorAbstract
{
    private const IGNORE_ALL_MUTATIONS_ANNOTATION = '@infection-ignore-all';
    public function __construct(private ChangingIgnorer $changingIgnorer, private SplObjectStorage $ignoredNodes)
    {
    }
    public function enterNode(Node $node) : ?Node
    {
        foreach ($node->getComments() as $comment) {
            if (str_contains($comment->getText(), self::IGNORE_ALL_MUTATIONS_ANNOTATION)) {
                $this->changingIgnorer->startIgnoring();
                $this->ignoredNodes->attach($node);
            }
        }
        return null;
    }
    public function leaveNode(Node $node) : ?Node
    {
        if ($this->ignoredNodes->contains($node)) {
            $this->ignoredNodes->detach($node);
            $this->changingIgnorer->stopIgnoring();
        }
        return null;
    }
}
