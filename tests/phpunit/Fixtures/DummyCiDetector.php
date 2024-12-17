<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Tests\UnsupportedMethod;
use OndraM\CiDetector\Ci\CiInterface;
use OndraM\CiDetector\Ci\GitHubActions;
use OndraM\CiDetector\CiDetectorInterface;
use OndraM\CiDetector\Env;
use OndraM\CiDetector\Exception\CiNotDetectedException;

final readonly class DummyCiDetector implements CiDetectorInterface
{
    public function __construct(private bool $ciDetected, private bool $githubActionsDetected = false)
    {
    }

    public static function fromEnvironment(Env $environment): CiDetectorInterface
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function isCiDetected(): bool
    {
        return $this->ciDetected;
    }

    public function detect(): CiInterface
    {
        if ($this->githubActionsDetected) {
            return new GitHubActions(new Env());
        }

        throw new CiNotDetectedException('No CI server detected in current environment');
    }
}
