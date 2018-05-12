<?php

namespace Infection\Tests\Fixtures\Autoloaded\Finder;


use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\PathFilterIterator;

class MockRelativePathFinder extends Finder
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
            $iterator = new PathFilterIterator($iterator, $this->filters, []);
        }

        return $iterator;
    }

}
