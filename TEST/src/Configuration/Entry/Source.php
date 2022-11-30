<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Entry;

use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class Source
{
    private array $directories;
    private array $excludes;
    public function __construct(array $directories, array $excludes)
    {
        Assert::allString($directories);
        Assert::allString($excludes);
        $this->directories = $directories;
        $this->excludes = $excludes;
    }
    public function getDirectories() : array
    {
        return $this->directories;
    }
    public function getExcludes() : array
    {
        return $this->excludes;
    }
}
