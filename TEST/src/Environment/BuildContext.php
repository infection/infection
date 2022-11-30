<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Environment;

final class BuildContext
{
    public function __construct(private string $repositorySlug, private string $branch)
    {
    }
    public function repositorySlug() : string
    {
        return $this->repositorySlug;
    }
    public function branch() : string
    {
        return $this->branch;
    }
}
