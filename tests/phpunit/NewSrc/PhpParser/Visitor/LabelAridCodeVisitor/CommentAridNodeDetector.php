<?php

declare(strict_types=1);

namespace Infection\Tests\NewSrc\PhpParser\Visitor\LabelAridCodeVisitor;

use newSrc\AST\AridCodeDetector\AridCodeDetector;
use PhpParser\Comment;
use PhpParser\Node;
use function iter\any;
use function str_contains;

final class CommentAridNodeDetector implements AridCodeDetector
{
    public const ARID_START_COMMENT = 'ARID_START';
    public const ARID_END_COMMENT = 'ARID_END';

    private bool $arid = false;

    public function isArid(Node $node): bool
    {
        if (self::shouldStartMarkingNodeAsArid($node)) {
            $this->arid = true;
        }

        if (self::shouldStopMarkingNodeAsArid($node)) {
            $this->arid = false;
        }

        return $this->arid;
    }

    private static function shouldStartMarkingNodeAsArid(Node $node): bool
    {
        return any(
            static fn (Comment $comment) => str_contains(
                $comment->getText(),
                self::ARID_START_COMMENT,
            ),
            $node->getComments(),
        );
    }

    private static function shouldStopMarkingNodeAsArid(Node $node): bool
    {
        return any(
            static fn (Comment $comment) => str_contains(
                $comment->getText(),
                self::ARID_END_COMMENT,
            ),
            $node->getComments(),
        );
    }
}
