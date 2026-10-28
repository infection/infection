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

namespace Infection\PhpParser\Visitor;

use function array_flip;
use function array_key_exists;
use function array_unique;
use function array_values;
use function explode;
use Generator;
use Infection\Mutator\ProfileList;
use Override;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use function Pipeline\take;
use Psr\Log\LoggerInterface;
use function Safe\preg_match_all;
use function sprintf;
use function trim;

/**
 * Attaches mutator exclusions from comments to the annotated node and its descendants.
 * Runs after ParentConnectingVisitor so each node can inherit its parent's exclusions.
 * Other mutators can still apply to these nodes.
 *
 * @internal
 */
final class ExcludeIgnoredMutatorsVisitor extends NodeVisitorAbstract
{
    public const string IGNORED_MUTATORS = 'ignoredMutators';

    private const string IGNORE_ANNOTATION_PATTERN = '/@infection-ignore(?=[ \t\r\n]|\*\/|$)[ \t]*([^\r\n]*?)(?=\*\/|[\r\n]|$)/';

    /**
     * @var array<string, mixed>
     */
    private readonly array $knownMutatorNamesByKey;

    /**
     * @param list<string> $knownMutatorNames
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $filePath,
        array $knownMutatorNames,
    ) {
        $this->knownMutatorNamesByKey = array_flip($knownMutatorNames);
    }

    #[Override]
    public function enterNode(Node $node): null
    {
        $annotatedMutators = $this->findIgnoredMutators($node);

        $this->warnAboutUnknownMutators($node, $annotatedMutators);

        $inheritedMutators = self::getInheritedIgnoredMutators($node);

        $ignoredMutators = array_values(
            array_unique([
                ...$inheritedMutators,
                ...$annotatedMutators,
            ]),
        );

        if ($ignoredMutators !== []) {
            $node->setAttribute(self::IGNORED_MUTATORS, $ignoredMutators);
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function getIgnoredMutators(Node $node): array
    {
        return $node->getAttribute(self::IGNORED_MUTATORS, []);
    }

    /**
     * @return list<string>
     */
    private function findIgnoredMutators(Node $node): array
    {
        if ($node->getComments() === []) {
            // False positive: this mutation affects performance without changing behaviour.
            // @infection-ignore ReturnRemoval
            return [];
        }

        return take($node->getComments())
            ->map(self::findAnnotationsInComment(...))
            ->map(fn (string $names) => $this->parseMutatorNames($node, $names))
            ->toList()
        ;
    }

    /**
     * @return list<string>
     */
    private static function getInheritedIgnoredMutators(Node $node): array
    {
        $parent = ParentConnector::findParent($node);

        if ($parent === null) {
            return [];
        }

        return self::getIgnoredMutators($parent);
    }

    /**
     * @return Generator<int, string>
     */
    private static function findAnnotationsInComment(Comment $comment): Generator
    {
        $matches = [];

        preg_match_all(
            self::IGNORE_ANNOTATION_PATTERN,
            $comment->getText(),
            $matches,
        );

        /** @var array{list<string>, list<string>} $matches */
        yield from $matches[1];
    }

    /**
     * @return Generator<int, string>
     */
    private function parseMutatorNames(Node $node, string $names): Generator
    {
        $mutatorNames = take(explode(',', $names))
            ->cast(trim(...))
            ->filter(static fn (string $name): bool => $name !== '')
            ->toList()
        ;

        if ($mutatorNames === []) {
            $this->logger->warning(
                sprintf(
                    'No mutator names in @infection-ignore at %s:%d.',
                    $this->filePath,
                    $node->getStartLine(),
                ),
            );
        }

        yield from $mutatorNames;
    }

    /**
     * @param list<string> $mutatorNames
     */
    private function warnAboutUnknownMutators(Node $node, array $mutatorNames): void
    {
        foreach (array_unique($mutatorNames) as $mutatorName) {
            if ($this->isKnownMutator($mutatorName)) {
                continue;
            }

            $this->logger->warning(
                sprintf(
                    'Unknown mutator "%s" in @infection-ignore at %s:%d.',
                    $mutatorName,
                    $this->filePath,
                    $node->getStartLine(),
                ),
            );
        }
    }

    private function isKnownMutator(string $mutatorName): bool
    {
        return array_key_exists($mutatorName, ProfileList::ALL_MUTATORS)
            || array_key_exists($mutatorName, $this->knownMutatorNamesByKey);
    }
}
