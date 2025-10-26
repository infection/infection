<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Finder;

use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;
use ReturnTypeWillChange;

final class MockSplFileInfo extends SplFileInfo
{
    private string|false $realPath;

    public function __construct($param)
    {
        if (\is_string($param)) {
            parent::__construct($param, $param, $param);
        } elseif (\is_array($param)) {
            $defaults = [
                'name' => 'file.txt',
                'realPath' => false,
                'relativePath' => '',
                'relativePathname' => '',
            ];
            $defaults = array_merge($defaults, $param);
            parent::__construct($defaults['name'], $defaults['relativePath'], $defaults['name']);

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
