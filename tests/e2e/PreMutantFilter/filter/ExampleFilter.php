<?php

namespace PreMutantFilter;

use Infection\Plugins\MutantFilterPlugin;
use Infection\Plugins\Configuration;
use Infection\Plugins\Mutant;

class ExampleFilter implements MutantFilterPlugin
{
    private $count;

    private function __construct()
    {
        $this->count = 0;
    }

    public static function create(Configuration $configuration)
    {
        // This Configuration object doesn't do anything yet, but it leave a
        // possibility to export anything we want from the original container,
        // or add some plugin-specific configuration options.
        return new static();
    }

    public function getMutantFilter(): ?callable
    {
        return function (Mutant $mutant) {
            // Do whatever there's need to be done.
            $mutant->getFilePath();
            $mutant->getMutatedCode();
            $mutant->getDiff();
            $mutant->isCoveredByTest();
            $mutant->getMutatorName();

            ++$this->count;

            // Allow only two first mutants
            return $this->count <= 2;
        };
    }
}
