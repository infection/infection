<?php

declare(strict_types=1);

namespace Infection\Git;

use Infection\Process\ShellCommandLineExecutor;
use RuntimeException;
use function array_slice;
use function count;
use function explode;
use function implode;

final readonly class CommandLineGit implements Git
{
    private const NUM_ORIGIN_AND_BRANCH_PARTS = 2;
    // TODO: maybe the default base could be configured in the config file?
    private const DEFAULT_BASE = 'origin/master';

    public function __construct(
        private ShellCommandLineExecutor $shellCommandLineExecutor,
    ) {
    }

    public function getDefaultBase(): string
    {
        // see https://www.reddit.com/r/git/comments/jbdb7j/comment/lpdk30e/
        try {
            $gitRefs = $this->shellCommandLineExecutor->execute([
                'git',
                'symbolic-ref',
                'refs/remotes/origin/HEAD',
            ]);

            $parts = explode('/', $gitRefs);

            if (count($parts) > self::NUM_ORIGIN_AND_BRANCH_PARTS) {
                // extract origin/branch from a string like 'refs/remotes/origin/master'
                return implode(
                    '/',
                    array_slice($parts, -self::NUM_ORIGIN_AND_BRANCH_PARTS),
                );
            }
        } catch (RuntimeException) {
            // e.g. no symbolic ref might be configured for a remote named "origin"
        }

        // unable to figure it out, return the default
        return self::DEFAULT_BASE;
    }

    public function getDefaultBaseFilter(): string
    {
        return 'AM';
    }
}