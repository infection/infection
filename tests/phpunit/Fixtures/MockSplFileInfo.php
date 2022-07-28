<?php

namespace Infection\Tests\Fixtures;

use RuntimeException;
use SplFileInfo;
use ReturnTypeWillChange;

final class MockSplFileInfo extends SplFileInfo
{
    private string|bool $realPath;

    public function __construct($param)
    {
        if (\is_string($param)) {
            parent::__construct($param);
        } elseif (\is_array($param)) {
            $defaults = [
                'name' => 'file.txt',
                'realPath' => false,
            ];
            $defaults = array_merge($defaults, $param);
            parent::__construct($defaults['name']);

            $this->realPath = $defaults['realPath'];
        } else {
            throw new RuntimeException(sprintf('Incorrect parameter "%s"', $param));
        }
    }

    #[ReturnTypeWillChange]
    public function getRealPath()
    {
        return $this->realPath;
    }
}
