<?php

declare(strict_types=1);

namespace Infection\Tests\Architecture\PHPat\Selector\HasInheritDoc\Fixtures;

class ParentWithInheritDoc
{
    /**
     * @inheritDoc
     *
     * @throws \RuntimeException
     */
    public function execute(): void
    {
    }
}
