<?php

declare(strict_types=1);

namespace Infection\CI;

use OndraM\CiDetector\Ci\CiInterface;
use OndraM\CiDetector\CiDetector;
use OndraM\CiDetector\Env;
use OndraM\CiDetector\Exception\CiNotDetectedException;

final class NullCiDetector extends CiDetector
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function fromEnvironment(Env $environment): CiDetector
    {
        return new self();
    }

    public function isCiDetected(): bool
    {
        return false;
    }

    public function detect(): CiInterface
    {
        throw new CiNotDetectedException('No CI server detectable with this detector');
    }
}
