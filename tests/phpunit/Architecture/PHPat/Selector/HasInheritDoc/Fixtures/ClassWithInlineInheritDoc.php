<?php

declare(strict_types=1);

namespace Infection\Tests\Architecture\PHPat\Selector\HasInheritDoc\Fixtures;

final class ClassWithInlineInheritDoc
{
    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     */
    public function execute(): void
    {
    }
}
