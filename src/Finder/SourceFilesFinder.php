<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Finder;

use Symfony\Component\Finder\Finder;

class SourceFilesFinder
{
    private $sourceDirectories;
    private $excludeDirectories;

    /**
     * @param string[] $sourceDirectories
     * @param string[] $excludeDirectories
     */
    public function __construct(array $sourceDirectories, array $excludeDirectories)
    {
        $this->sourceDirectories = $sourceDirectories;
        $this->excludeDirectories = $excludeDirectories;
    }

    public function getSourceFiles(string $filter = ''): Finder
    {
        $finder = new Finder();

        array_walk($this->excludeDirectories, function ($excludePath) use ($finder) {
            $finder->notPath($excludePath);
        });

        if ('' === $filter) {
            $finder->in($this->sourceDirectories)->files()->name('*.php')->contains('class ');

            return $finder;
        }

        $finder->in('.')->files()->contains('class ');

        $filters = explode(',', $filter);
        array_walk($filters, function ($fileFilter) use ($finder) {
            $finder->path($fileFilter);
        });

        return $finder;
    }
}
