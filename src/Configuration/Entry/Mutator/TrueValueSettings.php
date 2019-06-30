<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry\Mutator;

final class TrueValueSettings
{
    private $inArray;
    private $arraySearch;

    public function __construct(bool $inArray, bool $arraySearch)
    {
        $this->inArray = $inArray;
        $this->arraySearch = $arraySearch;
    }

    public function isInArray(): bool
    {
        return $this->inArray;
    }

    public function isArraySearch(): bool
    {
        return $this->arraySearch;
    }
}