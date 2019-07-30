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

namespace Infection\Finder;

use Infection\Finder\Iterator\RealPathFilterIterator;
use Iterator;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
final class SourceFilesFinder extends Finder
{
    /**
     * @var string[]
     */
    private $sourceDirectories;

    /**
     * @var string[]
     */
    private $excludeDirectories;

    /**
     * @var string[]
     */
    private $filters = [];

    public function __construct(array $sourceDirectories, array $excludeDirectories)
    {
        parent::__construct();

        $this->sourceDirectories = $sourceDirectories;
        $this->excludeDirectories = $excludeDirectories;
    }

    public function getSourceFiles(string $filter = ''): Finder
    {
        foreach ($this->excludeDirectories as $excludeDirectory) {
            $this->notPath($excludeDirectory);
        }

        $this->in($this->sourceDirectories)->files();

        if ('' === $filter) {
            $this->name('*.php');

            return $this;
        }

        $filters = array_filter(explode(',', $filter));

        foreach ($filters as $filter) {
            $this->filters[] = $filter;
        }

        return $this;
    }

    /**
     * @return RealPathFilterIterator|(iterable<\Symfony\Component\Finder\SplFileInfo>&Iterator)
     */
    public function getIterator()
    {
        $iterator = parent::getIterator();

        if ($this->filters) {
            $iterator = new RealPathFilterIterator($iterator, $this->filters, []);
        }

        return $iterator;
    }
}
