<?php

namespace Infection\Tests\Fixtures\Finder;

use Infection\FileSystem\Finder\Iterator\RealPathFilterIterator;
use Symfony\Component\Finder\Finder;

class MockRealPathFinder extends Finder
{
    private $sourceDirectories;

    private $filters = [];

    public function __construct(array $sourceDirectories)
    {
        parent::__construct();

        $this->sourceDirectories = $sourceDirectories;
    }

    public function setFilter(array $filter): self
    {
        $this->in($this->sourceDirectories)->files();

        $this->filters = $filter;

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
