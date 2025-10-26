<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Mutator;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_search;
use function array_values;
use function explode;
use function sprintf;
use UnexpectedValueException;

/**
 * @internal
 */
final class MutatorParser
{
    /**
     * @throws UnexpectedValueException
     *
     * @return string[]
     */
    public function parse(string $unparsedMutators): array
    {
        if ($unparsedMutators === '') {
            return [];
        }

        $parsedMutators = array_filter(array_map(
            'trim',
            explode(',', $unparsedMutators),
        ));

        foreach ($parsedMutators as $index => $profileOrMutator) {
            if (array_key_exists($profileOrMutator, ProfileList::ALL_PROFILES)) {
                continue;
            }

            if (array_key_exists($profileOrMutator, ProfileList::ALL_MUTATORS)) {
                continue;
            }

            $mutatorShortName = array_search(
                $profileOrMutator,
                ProfileList::ALL_MUTATORS,
                true,
            );

            if ($mutatorShortName !== false) {
                $parsedMutators[$index] = $mutatorShortName;

                continue;
            }

            if (MutatorResolver::isValidMutator($profileOrMutator)) {
                continue;
            }

            throw new UnexpectedValueException(
                sprintf(
                    'Expected "%s" to be a known mutator or profile. See "%s" and "%s" for '
                    . 'the list of available mutants and profiles.',
                    $profileOrMutator,
                    'https://infection.github.io/guide/mutators.html',
                    'https://infection.github.io/guide/profiles.html',
                ),
            );
        }

        return array_values($parsedMutators);
    }
}
