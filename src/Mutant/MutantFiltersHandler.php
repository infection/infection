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

namespace Infection\Mutant;

use Infection\Plugins\Mutant as PublicMutant;
use Infection\Plugins\MutantFilterPlugin;
use Pipeline\Interfaces\StandardPipeline as MutantPipeline;

/**
 * @internal
 * @final
 */
class MutantFiltersHandler
{
    /**
     * @var array<callable>
     * @psalm-var array<callable(PublicMutant): bool>
     */
    private $filters;

    /**
     * @param array<MutantFilterPlugin> $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = [];

        foreach ($filters as $filter) {
            $filterCallable = $filter->getMutantFilter();

            if ($filterCallable !== null) {
                $this->filters[] = $filterCallable;
            }
        }
    }

    /**
     * @param MutantPipeline<Mutant> $mutants
     */
    public function applyFilters(MutantPipeline $mutants): void
    {
        foreach ($this->filters as $filterCallback) {
            $mutants->filter(static function (Mutant $mutant) use ($filterCallback): bool {
                return $filterCallback($mutant->getMutantWrapper());
            });
        }
    }
}
