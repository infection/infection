<?php

declare(strict_types=1);

namespace Infection\Mutator;

use Infection\Configuration\Mutator\ArrayItemRemoval as ArrayItemRemovalConfig;
use Infection\Configuration\Mutator\BCMath as BCMathConfig;
use Infection\Configuration\Mutator\GenericMutator;
use Infection\Configuration\Mutator\MBString as MBStringConfig;
use Infection\Configuration\Mutator\MutatorConfiguration;
use Infection\Configuration\Mutator\TrueValue as TrueValueConfig;
use Infection\Mutator\Boolean\TrueValue as TrueValueMutator;
use Infection\Mutator\Extensions\BCMath as BCMathMutator;
use Infection\Mutator\Extensions\MBString as MBStringMutator;
use Infection\Mutator\Removal\ArrayItemRemoval as ArrayItemRemovalMutator;

final class MutatorConfigFactory
{
    public function create(string $mutatorClass, array $rawConfig): MutatorConfiguration
    {
        switch ($mutatorClass) {
            case ArrayItemRemovalMutator::class:
                return ArrayItemRemovalConfig::createFromRaw(
                    $rawConfig['ignore'] ?? [],
                        $rawConfig['settings'] ?? []
                );

            case BCMathMutator::class:
                return BCMathConfig::createFromRaw(
                    $rawConfig['ignore'] ?? [],
                        $rawConfig['settings'] ?? []
                );

            case MBStringMutator::class:
                return MBStringConfig::createFromRaw(
                    $rawConfig['ignore'] ?? [],
                        $rawConfig['settings'] ?? []
                );

            case TrueValueMutator::class:
                return TrueValueConfig::createFromRaw(
                    $rawConfig['ignore'] ?? [],
                        $rawConfig['settings'] ?? []
                );
        }

        return GenericMutator::createFromRaw($rawConfig['ignore'] ?? []);
    }
}
