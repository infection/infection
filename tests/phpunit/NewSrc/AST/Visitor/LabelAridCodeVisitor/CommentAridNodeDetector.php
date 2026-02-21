<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\NewSrc\AST\Visitor\LabelAridCodeVisitor;

use function iter\any;
use newSrc\AST\AridCodeDetector\AridCodeDetector;
use PhpParser\Comment;
use PhpParser\Node;
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
