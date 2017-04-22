<?php

declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Finder\TestFrameworkExecutableFinder;
use Infection\TestFramework\PhpSpec\Adapter\PhpSpecAdapter;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class Factory
{
    /**
     * @var string
     */
    private $tempDir;
    /**
     * @var PathReplacer
     */
    private $pathReplacer;

    public function __construct(string $tempDir, PathReplacer $pathReplacer)
    {
        $this->tempDir = $tempDir;
        $this->pathReplacer = $pathReplacer;
    }

    public function create($adapterName) : AbstractTestFrameworkAdapter
    {
        if ($adapterName === PhpUnitAdapter::NAME) {
            return new PhpUnitAdapter(
                new TestFrameworkExecutableFinder(PhpUnitAdapter::NAME),
                new InitialConfigBuilder($this->tempDir, 'phpunit.xml', $this->pathReplacer),
                new MutationConfigBuilder($this->tempDir, '/Users/user/tmp/remove/phpunit.xml'), // TODO replace hardcoded path
                new ArgumentsAndOptionsBuilder()
            );
        }

        if ($adapterName === PhpSpecAdapter::NAME) {
            return new PhpSpecAdapter(
                new TestFrameworkExecutableFinder(PhpSpecAdapter::NAME)
            );
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid name of test framework. Available names are: %s',
                implode(', ', [PhpUnitAdapter::NAME, PhpSpecAdapter::NAME])
            )
        );
    }
}