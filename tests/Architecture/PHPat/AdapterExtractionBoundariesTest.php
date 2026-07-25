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

namespace Infection\Tests\Architecture\PHPat;

use Infection\Tests\Architecture\PHPat\Selector\InfectionSelector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

final class AdapterExtractionBoundariesTest
{
    public function testMagoAdapterStaysWithinFuturePackageBoundary(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::magoAdapterCandidate())
            ->canOnly()
            ->dependOn()
            ->classes(
                InfectionSelector::magoAdapterCandidate(),
                InfectionSelector::testFrameworkContractCandidate(),
                InfectionSelector::adapterCommonCandidate(),
                // Note that this is only true because adapters are not available
                // here, otherwise we would need to be stricter.
                InfectionSelector::nonSourceCode(),
            )
            ->because('A future Mago package should stay within its package boundary: the adapter itself, the test framework contracts, shared adapter utilities, and external packages.');
    }

    public function testPHPStanAdapterStaysWithinFuturePackageBoundary(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::phpStanAdapterCandidate())
            ->canOnly()
            ->dependOn()
            ->classes(
                InfectionSelector::phpStanAdapterCandidate(),
                InfectionSelector::testFrameworkContractCandidate(),
                InfectionSelector::adapterCommonCandidate(),
                // Note that this is only true because adapters are not available
                // here, otherwise we would need to be stricter.
                InfectionSelector::nonSourceCode(),
            )
            ->because('A future PHPStan package should stay within its package boundary: the adapter itself, the test framework contracts, shared adapter utilities, and external packages.');
    }

    public function testTestFrameworkContractsStayWithinFuturePackageBoundary(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::testFrameworkContractCandidate())
            ->canOnly()
            ->dependOn()
            ->classes(
                InfectionSelector::testFrameworkContractCandidate(),
                // Note that this is only true because adapters are not available
                // here, otherwise we would need to be stricter.
                InfectionSelector::nonSourceCode(),
            )
            ->because('The future test framework contracts package should stay within its package boundary: the contracts themselves and external packages.');
    }

    public function testPHPUnitAdapterStaysWithinFuturePackageBoundary(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::phpUnitAdapterCandidate())
            ->canOnly()
            ->dependOn()
            ->classes(
                InfectionSelector::phpUnitAdapterCandidate(),
                InfectionSelector::testFrameworkContractCandidate(),
                InfectionSelector::adapterCommonCandidate(),
                // Note that this is only true because adapters are not available
                // here, otherwise we would need to be stricter.
                InfectionSelector::nonSourceCode(),
            )
            ->because('A future PHPUnit package should stay within its package boundary: the adapter itself, the test framework contracts, shared adapter utilities, infection/abstract-testframework-adapter, and external packages.');
    }

    public function testTestFrameworkCommonUtilitiesStayWithinFuturePackageBoundary(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::adapterCommonCandidate())
            ->canOnly()
            ->dependOn()
            ->classes(
                InfectionSelector::adapterCommonCandidate(),
                InfectionSelector::testFrameworkContractCandidate(),
                // Note that this is only true because adapters are not available
                // here, otherwise we would need to be stricter.
                InfectionSelector::nonSourceCode(),
            )
            ->because('A future test framework common package should stay within its package boundary: the common utilities themselves, the test framework contracts, and external packages.');
    }
}
