<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\Env;
abstract class AbstractCi implements CiInterface
{
    protected $env;
    public function __construct(Env $env)
    {
        $this->env = $env;
    }
    public function describe() : array
    {
        return ['ci-name' => $this->getCiName(), 'build-number' => $this->getBuildNumber(), 'build-url' => $this->getBuildUrl(), 'commit' => $this->getCommit(), 'branch' => $this->getBranch(), 'target-branch' => $this->getTargetBranch(), 'repository-name' => $this->getRepositoryName(), 'repository-url' => $this->getRepositoryUrl(), 'is-pull-request' => $this->isPullRequest()->describe()];
    }
}
