<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Process;

use _HumbugBox9658796bb9f0\Symfony\Component\Process\Exception\LogicException;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Exception\RuntimeException;
class PhpProcess extends Process
{
    public function __construct(string $script, string $cwd = null, array $env = null, int $timeout = 60, array $php = null)
    {
        if (null === $php) {
            $executableFinder = new PhpExecutableFinder();
            $php = $executableFinder->find(\false);
            $php = \false === $php ? null : \array_merge([$php], $executableFinder->findArguments());
        }
        if ('phpdbg' === \PHP_SAPI) {
            $file = \tempnam(\sys_get_temp_dir(), 'dbg');
            \file_put_contents($file, $script);
            \register_shutdown_function('unlink', $file);
            $php[] = $file;
            $script = null;
        }
        parent::__construct($php, $cwd, $env, $script, $timeout);
    }
    public static function fromShellCommandline(string $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        throw new LogicException(\sprintf('The "%s()" method cannot be called when using "%s".', __METHOD__, self::class));
    }
    public function start(callable $callback = null, array $env = [])
    {
        if (null === $this->getCommandLine()) {
            throw new RuntimeException('Unable to find the PHP executable.');
        }
        parent::start($callback, $env);
    }
}
