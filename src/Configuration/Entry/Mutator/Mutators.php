<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry\Mutator;

use function array_keys;
use Webmozart\Assert\Assert;

final class Mutators
{
    public const PROFILES = [
        '@arithmetic',
        '@boolean',
        '@cast',
        '@conditional_boundary',
        '@conditional_negotiation',
        '@function_signature',
        '@number',
        '@operator',
        '@regex',
        '@removal',
        '@return_value',
        '@sort',
        '@zero_iteration',
        '@default',
    ];

    private $profiles;
    private $trueValue;
    private $arrayItemRemoval;
    private $bcMath;
    private $mbString;

    /**
     * @param array<string,bool> $profiles
     */
    public function __construct(
        array $profiles,
        ?MutatorConfiguration $trueValue,
        ?MutatorConfiguration $arrayItemRemoval,
        ?MutatorConfiguration $bcMath,
        ?MutatorConfiguration $mbString
    ) {
        Assert::allOneOf(array_keys($profiles), self::PROFILES);
        Assert::allBoolean($profiles);

        $this->profiles = $profiles;
        $this->trueValue = $trueValue;
        $this->arrayItemRemoval = $arrayItemRemoval;
        $this->bcMath = $bcMath;
        $this->mbString = $mbString;
    }

    /**
     * @return array<string,bool>
     */
    public function getProfiles(): array
    {
        return $this->profiles;
    }

    public function getTrueValue(): ?MutatorConfiguration
    {
        return $this->trueValue;
    }

    public function getArrayItemRemoval(): ?MutatorConfiguration
    {
        return $this->arrayItemRemoval;
    }

    public function getBcMath(): ?MutatorConfiguration
    {
        return $this->bcMath;
    }

    public function getMbString(): ?MutatorConfiguration
    {
        return $this->mbString;
    }
}