<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use _HumbugBox9658796bb9f0\Composer\Autoload\ClassLoader;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\ComposerExecutableFinder;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class AdapterInstaller
{
    public const OFFICIAL_ADAPTERS_MAP = [TestFrameworkTypes::CODECEPTION => 'infection/codeception-adapter', TestFrameworkTypes::PHPSPEC => 'infection/phpspec-adapter'];
    private const TIMEOUT = 120.0;
    public function __construct(private ComposerExecutableFinder $composerExecutableFinder)
    {
    }
    public function install(string $adapterName) : void
    {
        Assert::keyExists(self::OFFICIAL_ADAPTERS_MAP, $adapterName);
        $process = new Process([$this->composerExecutableFinder->find(), 'require', '--dev', self::OFFICIAL_ADAPTERS_MAP[$adapterName]]);
        $process->setTimeout(self::TIMEOUT);
        $process->run();
        $loader = new ClassLoader();
        $map = (require 'vendor/composer/autoload_psr4.php');
        foreach ($map as $namespace => $paths) {
            $loader->setPsr4($namespace, $paths);
        }
        $loader->register(\false);
    }
}
