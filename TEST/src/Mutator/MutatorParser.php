<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_search;
use function array_values;
use function explode;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use UnexpectedValueException;
final class MutatorParser
{
    public function parse(string $unparsedMutators) : array
    {
        if ($unparsedMutators === '') {
            return [];
        }
        $parsedMutators = array_filter(array_map('trim', explode(',', $unparsedMutators)));
        foreach ($parsedMutators as $index => $profileOrMutator) {
            if (array_key_exists($profileOrMutator, ProfileList::ALL_PROFILES)) {
                continue;
            }
            if (array_key_exists($profileOrMutator, ProfileList::ALL_MUTATORS)) {
                continue;
            }
            $mutatorShortName = array_search($profileOrMutator, ProfileList::ALL_MUTATORS, \true);
            if ($mutatorShortName !== \false) {
                $parsedMutators[$index] = $mutatorShortName;
                continue;
            }
            throw new UnexpectedValueException(sprintf('Expected "%s" to be a known mutator or profile. See "%s" and "%s" for ' . 'the list of available mutants and profiles.', $profileOrMutator, 'https://infection.github.io/guide/mutators.html', 'https://infection.github.io/guide/profiles.html'));
        }
        return array_values($parsedMutators);
    }
}
