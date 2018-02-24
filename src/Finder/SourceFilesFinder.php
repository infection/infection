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

        $filters = explode(',', $filter);
        foreach ($filters as $filter) {
            $this->filters[] = $filter;
        }

        return $this;
    }

    public function getIterator()
    {
        $iterator = parent::getIterator();

        if ($this->filters) {
            $iterator = new RealPathFilterIterator($iterator, $this->filters, []);
        }

        return $iterator;
    }
}
