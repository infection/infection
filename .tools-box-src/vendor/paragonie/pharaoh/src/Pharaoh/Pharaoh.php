<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\Pharaoh;

use _HumbugBoxb47773b41c19\ParagonIE\ConstantTime\Hex;
class Pharaoh
{
    public $phar;
    public $tmp;
    public static $stubfile;
    public function __construct(string $file, $alias = null)
    {
        if (!\is_readable($file)) {
            throw new PharError($file . ' cannot be read');
        }
        if (\ini_get('phar.readonly') == '1') {
            throw new PharError("Pharaoh cannot be used if phar.readonly is enabled in php.ini\n");
        }
        if (empty(self::$stubfile)) {
            self::$stubfile = Hex::encode(\random_bytes(12)) . '.pharstub';
        }
        if (empty($alias)) {
            $alias = Hex::encode(\random_bytes(16)) . '.phar';
        }
        if (!\copy($file, $tmpFile = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . $alias)) {
            throw new \Error('Could not create temporary file');
        }
        $this->phar = new \Phar($tmpFile);
        $this->phar->setAlias($alias);
        $tmp = \tempnam(\sys_get_temp_dir(), 'phr_');
        if (!\is_string($tmp)) {
            throw new \Error('Could not create temporary file');
        }
        $this->tmp = $tmp;
        \unlink($this->tmp);
        if (!\mkdir($this->tmp, 0755, \true) && !\is_dir($this->tmp)) {
            throw new \Error('Could not create temporary directory');
        }
        $this->phar->extractTo($this->tmp);
        \file_put_contents($this->tmp . '/' . self::$stubfile, $this->phar->getStub());
    }
    public function __destruct()
    {
        $path = $this->phar->getPath();
        unset($this->phar);
        \Phar::unlinkArchive($path);
    }
}
