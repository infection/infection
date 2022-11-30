<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class Definition
{
    private string $category;
    public function __construct(private string $description, string $category, private ?string $remedies, private string $diff)
    {
        Assert::oneOf($category, MutatorCategory::ALL);
        $this->category = $category;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function getCategory() : string
    {
        return $this->category;
    }
    public function getRemedies() : ?string
    {
        return $this->remedies;
    }
    public function getDiff() : string
    {
        return $this->diff;
    }
}
