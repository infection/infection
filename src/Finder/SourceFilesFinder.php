<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Finder;

use Infection\Finder\Iterator\RealPathFilterIterator;
use Symfony\Component\Finder\Finder;

class SourceFilesFinder extends Finder
{
    private $sourceDirectories;
    private $excludeDirectories;
    private $filter = [];

    /**
     * @param string[] $sourceDirectories
     * @param string[] $excludeDirectories
     */
    public function __construct(array $sourceDirectories, array $excludeDirectories)
    {
        parent::__construct();

        $this->sourceDirectories = $sourceDirectories;
        $this->excludeDirectories = $excludeDirectories;
    }

    public function getSourceFiles(string $filter = ''): Finder
    {
        array_walk($this->excludeDirectories, function ($excludePath) {
            $this->notPath($excludePath);
        });

        $this->in($this->sourceDirectories)->files();

        if ('' === $filter) {
            $this->name('*.php');

            return $this;
        }

        $filters = explode(',', $filter);
        array_walk($filters, function ($fileFilter) {
            $this->realPath($fileFilter);
        });

        return $this;
    }

    public function getIterator()
    {
        $iterator = parent::getIterator();

        if ($this->filter) {
            $iterator = new RealPathFilterIterator($iterator, $this->filter, []);
        }

        return $iterator;
    }

    private function realPath(string $filter)
    {
        $this->filter[] = $filter;

        return $this;
    }
}
