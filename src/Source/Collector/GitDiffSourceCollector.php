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

namespace Infection\Source\Collector;

use function explode;
use function implode;
use Infection\Git\Git;
use Infection\Logger\GitHub\NoFilesInDiffToMutate;
use const PHP_EOL;

/**
 * @internal
 */
final class GitDiffSourceCollector implements SourceCollector
{
    private ?SourceCollector $innerCollector = null;

    /**
     * @param non-empty-string $filter
     * @param non-empty-string[] $sourceDirectories
     * @param non-empty-string[] $excludedDirectoriesOrFiles
     */
    public function __construct(
        private readonly Git $git,
        private readonly string $filter,
        private readonly string $baseBranch,
        private readonly array $sourceDirectories,
        private readonly array $excludedDirectoriesOrFiles,
    ) {
    }

    public function collect(): iterable
    {
        return $this->getInnerCollector()->collect();
    }

    public function filter(iterable $input): iterable
    {
        return $this->getInnerCollector()->filter($input);
    }

    public function isFiltered(): bool
    {
        return true;
    }

    private function getInnerCollector(): SourceCollector
    {
        if ($this->innerCollector === null) {
            $filter = $this->getFilter();

            $this->innerCollector = SchemaSourceCollector::create(
                $filter,
                $this->sourceDirectories,
                $this->excludedDirectoriesOrFiles,
            );
        }

        return $this->innerCollector;
    }

    /**
     * @throws NoFilesInDiffToMutate
     *
     * @return non-empty-string
     */
    private function getFilter(): string
    {
        $referenceCommit = $this->git->findReferenceCommit($this->baseBranch);

        $filter = $this->git->diff(
            $referenceCommit,
            $this->filter,
            $this->sourceDirectories,
        );

        if ($filter === '') {
            throw NoFilesInDiffToMutate::create();
        }

        return implode(
            ',',
            explode(PHP_EOL, $filter),
        );
    }
}
