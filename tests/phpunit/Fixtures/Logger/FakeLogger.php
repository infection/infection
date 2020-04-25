<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Logger;

use Infection\Tests\UnsupportedMethod;
use Psr\Log\AbstractLogger;

final class FakeLogger extends AbstractLogger
{
    public function log($level, $message, array $context = []): void
    {
        throw UnsupportedMethod::method(__CLASS__, __METHOD__);
    }
}
