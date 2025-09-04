<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry;

use Webmozart\Assert\Assert;

final readonly class GitOptions
{
    public function __construct(
        public ?string $gitDiffFilter,
        public bool $isForGitDiffLines,
        public ?string $gitDiffBase,
    ) {
        if (null === $gitDiffFilter) {
            Assert::true($isForGitDiffLines);
        } else {
            Assert::false($isForGitDiffLines);
        }
    }
}
