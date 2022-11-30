<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\Pharaoh;

use _HumbugBoxb47773b41c19\ParagonIE\ConstantTime\Hex;
class PharDiff
{
    protected $c = ['' => "\x1b[0;39m", 'red' => "\x1b[0;31m", 'green' => "\x1b[0;32m", 'blue' => "\x1b[1;34m", 'cyan' => "\x1b[1;36m", 'silver' => "\x1b[0;37m", 'yellow' => "\x1b[0;93m"];
    private $phars = [];
    private $verbose = \false;
    public function __construct(Pharaoh $pharA, Pharaoh $pharB)
    {
        $this->phars = [$pharA, $pharB];
    }
    /**
    @psalm-suppress
    */
    public function printGitDiff() : int
    {
        $argA = \escapeshellarg($this->phars[0]->tmp);
        $argB = \escapeshellarg($this->phars[1]->tmp);
        $diff = `git diff --no-index {$argA} {$argB}`;
        echo $diff;
        if (empty($diff) && $this->verbose) {
            echo 'No differences encountered.', \PHP_EOL;
            return 0;
        }
        return 1;
    }
    /**
    @psalm-suppress
    */
    public function printGnuDiff() : int
    {
        $argA = \escapeshellarg($this->phars[0]->tmp);
        $argB = \escapeshellarg($this->phars[1]->tmp);
        $diff = `diff {$argA} {$argB}`;
        echo $diff;
        if (empty($diff) && $this->verbose) {
            echo 'No differences encountered.', \PHP_EOL;
            return 0;
        }
        return 1;
    }
    /**
     * Get hashes of all of the files in the two arrays.
     * 
     * @param string $algo
     * @param string $dirA
     * @param string $dirB
     * @return array<int, array<mixed, string>>
     * @throws \SodiumException
     */
    public function hashChildren(string $algo, string $dirA, string $dirB)
    {
        $a = $b = '';
        $filesA = $this->listAllFiles($dirA);
        $filesB = $this->listAllFiles($dirB);
        $numFiles = \max(\count($filesA), \count($filesB));
        $hashes = [[], []];
        for ($i = 0; $i < $numFiles; ++$i) {
            $thisFileA = (string) $filesA[$i];
            $thisFileB = (string) $filesB[$i];
            if (isset($filesA[$i])) {
                $a = \preg_replace('#^' . \preg_quote($dirA, '#') . '#', '', $thisFileA);
                if (isset($filesB[$i])) {
                    $b = \preg_replace('#^' . \preg_quote($dirB, '#') . '#', '', $thisFileB);
                } else {
                    $b = $a;
                }
            } elseif (isset($filesB[$i])) {
                $b = \preg_replace('#^' . \preg_quote($dirB, '#') . '#', '', $thisFileB);
                $a = $b;
            }
            if (isset($filesA[$i])) {
                if (\strtolower($algo) === 'blake2b') {
                    $hashes[0][$a] = Hex::encode(\_HumbugBoxb47773b41c19\ParagonIE_Sodium_File::generichash($thisFileA));
                } else {
                    $hashes[0][$a] = \hash_file($algo, $thisFileA);
                }
            } elseif (isset($filesB[$i])) {
                $hashes[0][$a] = '';
            }
            if (isset($filesB[$i])) {
                if (\strtolower($algo) === 'blake2b') {
                    $hashes[1][$b] = Hex::encode(\_HumbugBoxb47773b41c19\ParagonIE_Sodium_File::generichash($thisFileB));
                } else {
                    $hashes[1][$b] = \hash_file($algo, $thisFileB);
                }
            } elseif (isset($filesA[$i])) {
                $hashes[1][$b] = '';
            }
        }
        return $hashes;
    }
    private function listAllFiles($folder, $extension = '*')
    {
        /**
         * @var array<mixed, string> $fileList
         * @var string $i
         * @var string $file
         * @var \RecursiveDirectoryIterator $dir
         * @var \RecursiveIteratorIterator $ite
         */
        $dir = new \RecursiveDirectoryIterator($folder);
        $ite = new \RecursiveIteratorIterator($dir);
        if ($extension === '*') {
            $pattern = '/.*/';
        } else {
            $pattern = '/.*\\.' . $extension . '$/';
        }
        $files = new \RegexIterator($ite, $pattern, \RegexIterator::GET_MATCH);
        $fileList = [];
        foreach ($files as $fileSub) {
            $fileList = \array_merge($fileList, $fileSub);
        }
        foreach ($fileList as $i => $file) {
            if (\preg_match('#/\\.{1,2}$#', (string) $file)) {
                unset($fileList[$i]);
            }
        }
        return \array_values($fileList);
    }
    public function listChecksums(string $algo = 'sha384') : int
    {
        list($pharA, $pharB) = $this->hashChildren($algo, $this->phars[0]->tmp, $this->phars[1]->tmp);
        $diffs = 0;
        foreach (\array_keys($pharA) as $i) {
            if (isset($pharA[$i]) && isset($pharB[$i])) {
                if ($pharA[$i] !== $pharB[$i]) {
                    ++$diffs;
                    echo "\t", (string) $i, "\n\t\t", $this->c['red'], $pharA[$i], $this->c[''], "\t", $this->c['green'], $pharB[$i], $this->c[''], "\n";
                } elseif (!empty($pharA[$i]) && empty($pharB[$i])) {
                    ++$diffs;
                    echo "\t", (string) $i, "\n\t\t", $this->c['red'], $pharA[$i], $this->c[''], "\t", \str_repeat('-', \strlen($pharA[$i])), "\n";
                } elseif (!empty($pharB[$i]) && empty($pharA[$i])) {
                    ++$diffs;
                    echo "\t", (string) $i, "\n\t\t", \str_repeat('-', \strlen($pharB[$i])), "\t", $this->c['green'], $pharB[$i], $this->c[''], "\n";
                }
            }
        }
        if ($diffs === 0) {
            if ($this->verbose) {
                echo 'No differences encountered.', \PHP_EOL;
            }
            return 0;
        }
        return 1;
    }
    public function setVerbose(bool $value)
    {
        $this->verbose = $value;
    }
}
