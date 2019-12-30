<?php

declare(strict_types=1);

namespace Infection\Mutator;

use Webmozart\Assert\Assert;

final class Definition
{
    private $effectDescription;
    private $classification;
    private $remedies;

    public function __construct(
        string $effectDescription,
        string $classification,
        string $remedies
    ) {
        Assert::oneOf($classification, Classification::ALL);

        $this->effectDescription = $effectDescription;
        $this->classification = $classification;
        $this->remedies = $remedies;
    }

    public function getEffectDescription(): string
    {
        return $this->effectDescription;
    }

    public function getClassification(): string
    {
        return $this->classification;
    }

    public function getRemedies(): string
    {
        return $this->remedies;
    }
}
