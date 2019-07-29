<?php

declare(strict_types=1);

namespace Infection\Configuration;

use Infection\Locator\Locator;

final class ConfigurationLoader
{
    private $locator;
    private $fileLoader;

    public function __construct(Locator $locator, ConfigurationFileLoader $fileLoader)
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