<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use PhpParser\NodeVisitorAbstract;

final class RecordedVisitor extends NodeVisitorAbstract
{
    public $records;

    public function __construct(array &$records)
    {
        $this->records = &$records;
    }

    public function beforeTraverse(array $nodes): void
    {
        $this->records[] = $this;
    }
}
