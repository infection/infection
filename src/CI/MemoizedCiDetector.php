<?php

declare(strict_types=1);

namespace Infection\CI;

use OndraM\CiDetector\Ci\CiInterface;
use OndraM\CiDetector\CiDetector;
use OndraM\CiDetector\Env;

final class MemoizedCiDetector extends CiDetector
{
    private $decorated;

    /**
     * @var CiInterface|null|false
     */
    private $ci = false;

    public function __construct(Env $environment)
    {
        $this->decorated = new parent($environment);
    }

    public static function fromEnvironment(Env $environment): CiDetector
    {
        return new self($environment);
    }

    protected function detectCurrentCiServer(): ?CiInterface
    {
        if ($this->ci === false) {
            $this->ci = parent::detectCurrentCiServer();
        }

        return $this->ci;
    }

}
