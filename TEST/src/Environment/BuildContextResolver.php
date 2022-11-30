<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Environment;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Exception\CiNotDetectedException;
use function trim;
final class BuildContextResolver
{
    public function __construct(private CiDetector $ciDetector)
    {
    }
    public function resolve() : BuildContext
    {
        try {
            $ci = $this->ciDetector->detect();
        } catch (CiNotDetectedException) {
            throw new CouldNotResolveBuildContext('The current process is not executed in a CI build');
        }
        if ($ci->isPullRequest()->yes()) {
            throw new CouldNotResolveBuildContext('The current process is a pull request build');
        }
        if ($ci->isPullRequest()->maybe()) {
            throw new CouldNotResolveBuildContext('The current process may be a pull request build');
        }
        if (trim($ci->getRepositoryName()) === '') {
            throw new CouldNotResolveBuildContext('The repository name could not be determined for the current process');
        }
        if (trim($ci->getBranch()) === '') {
            throw new CouldNotResolveBuildContext('The branch name could not be determined for the current process');
        }
        return new BuildContext($ci->getRepositoryName(), $ci->getBranch());
    }
}
