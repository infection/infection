<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry;

use Webmozart\Assert\Assert;

final class Source
{
    private $directories;
    private $excludes;

    /**
     * @param string[] $directories
     * @param string[] $excludes
     */
    public function __construct(array $directories, array $excludes)
    {
        Assert::allString($directories);
        Assert::allString($excludes);

        $this->directories = $directories;
        $this->excludes = $excludes;
    }

    /**
     * @return string[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * @return string[]
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }
}