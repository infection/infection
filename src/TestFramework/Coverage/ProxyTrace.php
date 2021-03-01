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

namespace Infection\TestFramework\Coverage;

use Infection\TestFramework\Coverage\XmlReport\TestLocator;
use Later\Interfaces\Deferred;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * Full-pledge trace that acts as a proxy i.e. for which the tracing of the test files will be done
 * lazily.
 *
 * @internal
 * @final
 */
class ProxyTrace implements Trace
{
    private SplFileInfo $sourceFile;

    /**
     * @var ?Deferred<TestLocations>
     */
    private ?Deferred $lazyTestLocations;

    private ?TestLocator $tests = null;

    /**
     * @param Deferred<TestLocations> $lazyTestLocations
     */
    public function __construct(SplFileInfo $sourceFile, ?Deferred $lazyTestLocations = null)
    {
        $this->sourceFile = $sourceFile;

        // There's no point to have it parsed right away as we may not need it, e.g. because of a filter
        $this->lazyTestLocations = $lazyTestLocations;
    }

    public function getSourceFileInfo(): SplFileInfo
    {
        return $this->sourceFile;
    }

    public function getRealPath(): string
    {
        $realPath = $this->sourceFile->getRealPath();

        Assert::string($realPath);

        return $realPath;
    }

    public function getRelativePathname(): string
    {
        return $this->sourceFile->getRelativePathname();
    }

    public function hasTests(): bool
    {
        if ($this->lazyTestLocations === null) {
            return false;
        }

        return $this->getTestLocator()->hasTests();
    }

    public function getTests(): ?TestLocations
    {
        if ($this->lazyTestLocations !== null) {
            return $this->lazyTestLocations->get();
        }

        return null;
    }

    public function getAllTestsForMutation(NodeLineRangeData $lineRange, bool $isOnFunctionSignature): iterable
    {
        if ($this->lazyTestLocations === null) {
            return [];
        }

        return $this->getTestLocator()->getAllTestsForMutation($lineRange, $isOnFunctionSignature);
    }

    private function getTestLocator(): TestLocator
    {
        if ($this->tests !== null) {
            return $this->tests;
        }

        $testLocations = $this->getTests();

        Assert::notNull($testLocations);

        $this->tests = new TestLocator($testLocations);

        return $this->tests;
    }
}
