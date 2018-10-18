<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Util;

use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class MutatorParser
{
    /**
     * @var string|null
     */
    private $inputMutators;

    /**
     * @var array|Mutator[]
     */
    private $configMutators;

    public function __construct(?string $inputMutators, array $configMutators)
    {
        $this->inputMutators = $inputMutators;
        $this->configMutators = $configMutators;
    }

    /**
     * @return array|Mutator[]
     */
    public function getMutators(): array
    {
        $parsedMutators = $this->parseMutators();

        if (\count($parsedMutators) > 0) {
            $mutatorSettings = [];

            foreach ($parsedMutators as $mutatorName) {
                $mutatorSettings[$mutatorName] = true;
            }
            $generator = new MutatorsGenerator($mutatorSettings);

            return $generator->generate();
        }

        return $this->configMutators;
    }

    private function parseMutators(): array
    {
        if ($this->inputMutators === null) {
            return [];
        }

        $trimmedMutators = trim($this->inputMutators);
        Assert::notEmpty($trimmedMutators, 'The "--mutators" option requires a value.');

        return explode(',', $trimmedMutators);
    }
}
