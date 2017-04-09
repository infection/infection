<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Finder;

use Infection\Finder\AbstractExecutableFinder;
use Infection\Finder\ComposerExecutableFinder;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class PhpUnitExecutableFinder extends AbstractExecutableFinder
{
    /**
     * @return string
     */
    public function find()
    {
        $this->checkVendorPath();
        return $this->findPhpunit();
    }

    /**
     * @return void
     */
    private function checkVendorPath()
    {
        $vendorPath = null;
        try {
            $composer = $this->findComposer();
            $process = new Process(sprintf('%s %s', $composer, 'config bin-dir'));
            $process->run();
            $vendorPath = trim($process->getOutput());
        } catch (RuntimeException $e) {
            $candidate = getcwd() . '/vendor/bin';
            if (file_exists($candidate)) {
                $vendorPath = $candidate;
            }
        }
        if (!is_null($vendorPath)) {
            putenv('PATH=' . $vendorPath . PATH_SEPARATOR . getenv('PATH'));
        }
    }

    /**
     * @return string
     */
    private function findComposer()
    {
        $finder = new ComposerExecutableFinder();

        return $finder->find();
    }

    /**
     * @return string
     */
    private function findPhpunit()
    {
        $probable = ['phpunit', 'phpunit.phar'];
        $finder = new ExecutableFinder();

        foreach ($probable as $name) {
            if ($path = $finder->find($name, null, [getcwd()])) {
                return $this->makeExecutable($path);
            }
        }

        $result = $this->searchNonExecutables($probable, [getcwd()]);

        if (!is_null($result)) {
            return $result;
        }

        throw new \RuntimeException(
            'Unable to locate a PHPUnit executable on local system. Ensure '
            . 'that PHPUnit is installed and available.'
        );
    }

    /**
     * Prefix commands with exec outside Windows to ensure process timeouts
     * are enforced and end PHP processes properly
     *
     * @param string $path
     * @return string
     */
    protected function makeExecutable($path)
    {
        $path = realpath($path);
        $phpFinder = new PhpExecutableFinder();

        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            if (false !== strpos($path, '.bat')) {
                return $path;
            }
            return sprintf('%s %s', $phpFinder->find(), $path);
        }

        return sprintf('%s %s %s', 'exec', $phpFinder->find(), $path);
    }
}