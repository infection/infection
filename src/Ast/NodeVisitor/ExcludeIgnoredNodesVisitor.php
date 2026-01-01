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

namespace Infection\Ast\NodeVisitor;

use Infection\Ast\Metadata\Annotation;
use Infection\Ast\Metadata\NodeAnnotator;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use function iter\any;
use function str_contains;

// TODO: replaces IgnoreAllMutationsAnnotationReaderVisitor
/**
 * Excludes nodes that are annotated as to not mutate.
 */
final class ExcludeIgnoredNodesVisitor extends NodeVisitorAbstract
{
    private const IGNORE_ALL_MUTATIONS_ANNOTATION = '@infection-ignore-all';

    public function enterNode(Node $node): ?int
    {
        if (self::isAnnotatedWithIgnoreAll($node)) {
            NodeAnnotator::annotate($node, Annotation::IGNORED_WITH_ANNOTATION);

            return self::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        return null;
    }

    private static function isAnnotatedWithIgnoreAll(Node $node): bool
    {
        return any(
            self::commentContainsAnnotation(...),
            $node->getComments(),
        );
    }

    private static function commentContainsAnnotation(Comment $comment): bool
    {
        return str_contains(
            $comment->getText(),
            self::IGNORE_ALL_MUTATIONS_ANNOTATION,
        );
    }
}
