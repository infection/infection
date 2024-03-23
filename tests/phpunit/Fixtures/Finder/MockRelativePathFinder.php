<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Finder;

use Iterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\PathFilterIterator;

class MockRelativePathFinder extends Finder
{
    private array $filters = [];

    public function __construct(private readonly array $sourceDirectories)
    {
        parent::__construct();
    }

    public function setFilter(array $filter): self
    {
        $this->in($this->sourceDirectories)->files();

        $this->filters = $filter;

        return $this;
    }

    public function getIterator(): Iterator
    {
        $iterator = parent::getIterator();

        if ($this->filters) {
            $iterator = new PathFilterIterator($iterator, $this->filters, []);
        }

        return $iterator;
    }
}
