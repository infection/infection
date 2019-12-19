<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework;

use Infection\TestFramework\TestFrameworkExtraOptions;

final class DummyTestFrameworkExtraOptions extends TestFrameworkExtraOptions
{
    /**
     * @return string[]
     */
    protected function getInitialRunOnlyOptions(): array
    {
        return ['foo', 'bar'];
    }
}
