<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Finder;

class IgnoredMutatorsFinder
{
    /**
     * @var array
     */
    private $ignoreCases;

    /**
     * @var string
     */
    private $relativePath;

    /**
     * @var array
     */
    private $calculatedPaths;

    public function __construct(array $ignoreCases, string $relativePath)
    {
        $this->ignoreCases = $ignoreCases;
        $this->relativePath = $relativePath;
    }

    public function isIgnored(string $mutator, string $currentFile): bool
    {
        if (!isset($this->ignoreCases[$mutator])) {
            return false;
        }

        $toIgnore = (array) $this->ignoreCases[$mutator];

        if(in_array('*', $toIgnore)) {
            return true;
        }

        return isset($this->getCalculatedPaths()[$mutator][$currentFile]);
        //TODO: check methods
    }

    private function getCalculatedPaths(): array
    {
        if($this->calculatedPaths !== null) {
            return $this->calculatedPaths;
        }
        $calculated = [];

        foreach ($this->ignoreCases as $mutator => $paths) {
            if (!is_array($paths)) {
                $calculated[$mutator][$this->relativePath . '/' .$paths] = '*';
                continue;
            }
            foreach ((array)$paths as $key => $path) {
                if (!is_array($path)) {
                    $calculated[$mutator][$this->relativePath . '/' .$path] = '*';
                    continue;
                }
                foreach ($path as $method) {
                    $calculated[$mutator][$this->relativePath . '/' .$key] = $method;
                }
            }
        }
        $this->calculatedPaths = $calculated;

        return $this->calculatedPaths;
    }

}
