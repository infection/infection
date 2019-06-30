<?php

declare(strict_types=1);

namespace Infection\Configuration;

use Infection\Finder\LocatorInterface;

final class ConfigurationLoader
{
    private $locator;
    private $fileLoader;

    public function __construct(LocatorInterface $locator, ConfigurationFileLoader $fileLoader)
    {
        $this->locator = $locator;
        $this->fileLoader = $fileLoader;
    }

    public function loadConfiguration(array $potentialPaths): Configuration
    {
        return $this->fileLoader->loadFile(
            $this->locator->locateOneOf($potentialPaths)
        );
    }
}