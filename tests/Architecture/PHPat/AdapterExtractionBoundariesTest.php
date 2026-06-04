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
use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

final class AdapterExtractionBoundariesTest
{
    public function testStaticAnalysisContractDoesNotDependOnConcreteStaticAnalysisAdapters(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::staticAnalysisContractCandidate())
            ->shouldNot()
            ->dependOn()
            ->classes(InfectionSelector::staticAnalysisAdapterCandidate())
            ->because('The static analysis contract candidate must stay independent from concrete Mago and PHPStan adapters.');
    }

    public function testMagoAdapterOnlyDependsOnExtractionSurface(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::magoAdapterCandidate())
            ->canOnly()
            ->dependOn()
            ->classes(
                InfectionSelector::magoAdapterCandidate(),
                InfectionSelector::staticAnalysisContractCandidate(),
                InfectionSelector::testFrameworkContractCandidate(),
                InfectionSelector::adapterUtilityCandidate(),
                InfectionSelector::nonSourceCode(),
            )
            ->because('A future Mago package should only depend on the static analysis contract, shared extraction utilities, and external packages.');
    }

    public function testPHPStanAdapterOnlyDependsOnExtractionSurface(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::phpStanAdapterCandidate())
            ->canOnly()
            ->dependOn()
            ->classes(
                InfectionSelector::phpStanAdapterCandidate(),
                InfectionSelector::staticAnalysisContractCandidate(),
                InfectionSelector::testFrameworkContractCandidate(),
                InfectionSelector::adapterUtilityCandidate(),
                InfectionSelector::nonSourceCode(),
            )
            ->because('A future PHPStan package should only depend on the static analysis contract, shared extraction utilities, and external packages.');
    }

    public function testStaticAnalysisAdaptersDoNotDependOnEachOther(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::magoAdapterCandidate())
            ->shouldNot()
            ->dependOn()
            ->classes(InfectionSelector::phpStanAdapterCandidate())
            ->because('Static analysis adapters must remain independently extractable.');
    }

    public function testPHPStanAdapterDoesNotDependOnMagoAdapter(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::phpStanAdapterCandidate())
            ->shouldNot()
            ->dependOn()
            ->classes(InfectionSelector::magoAdapterCandidate())
            ->because('Static analysis adapters must remain independently extractable.');
    }

    public function testTestFrameworkContractDoesNotDependOnPHPUnitAdapter(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::testFrameworkContractCandidate())
            ->shouldNot()
            ->dependOn()
            ->classes(InfectionSelector::phpUnitAdapterCandidate())
            ->because('The test framework contract candidate must stay independent from the concrete PHPUnit adapter.');
    }

    public function testPHPUnitAdapterOnlyDependsOnExtractionSurface(): Rule
    {
        return PHPat::rule()
            ->classes(InfectionSelector::phpUnitAdapterCandidate())
            ->canOnly()
            ->dependOn()
            ->classes(
                InfectionSelector::phpUnitAdapterCandidate(),
                InfectionSelector::testFrameworkContractCandidate(),
                InfectionSelector::adapterUtilityCandidate(),
                Selector::inNamespace('Infection\AbstractTestFramework'),
                InfectionSelector::nonSourceCode(),
            )
            ->because('A future PHPUnit package should only depend on the test framework contract, shared extraction utilities, infection/abstract-testframework-adapter, and external packages.');
    }
}
