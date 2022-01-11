<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Tests\UnsupportedMethod;
use OndraM\CiDetector\Ci\CiInterface;
use OndraM\CiDetector\Ci\GitHubActions;
use OndraM\CiDetector\CiDetectorInterface;
use OndraM\CiDetector\Env;
use OndraM\CiDetector\Exception\CiNotDetectedException;

final class DummyCiDetector implements CiDetectorInterface
{
    private bool $ciDetected;
    private bool $githubActionsDetected;

    public function __construct(bool $ciDetected, bool $githubActionsDetected = false)
    {
        $this->ciDetected = $ciDetected;
        $this->githubActionsDetected = $githubActionsDetected;
    }

    public static function fromEnvironment(Env $environment): CiDetector
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
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
