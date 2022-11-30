<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Config\ValueProvider;

use _HumbugBox9658796bb9f0\Safe\Exceptions\InfoException;
use function _HumbugBox9658796bb9f0\Safe\ini_get;
class PCOVDirectoryProvider
{
    private ?string $phpConfiguredPcovDirectory;
    public function __construct(?string $iniValue = null)
    {
        try {
            $this->phpConfiguredPcovDirectory = $iniValue ?? ini_get('pcov.directory');
        } catch (InfoException $e) {
            $this->phpConfiguredPcovDirectory = null;
        }
    }
    public function shallProvide() : bool
    {
        return $this->phpConfiguredPcovDirectory === '';
    }
    public function getDirectory() : string
    {
        return '.';
    }
}
