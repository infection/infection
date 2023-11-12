<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Finder;

use Infection\FileSystem\Finder\Iterator\RealPathFilterIterator;
use Iterator;
use Symfony\Component\Finder\Finder;

class MockRealPathFinder extends Finder
{
    private array $filters = [];

    public function __construct(private array $sourceDirectories)
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
            $iterator = new RealPathFilterIterator($iterator, $this->filters, []);
        }

        return $iterator;
    }
}
