<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Process\Pipes;

interface PipesInterface
{
    public const CHUNK_SIZE = 16384;
    public function getDescriptors() : array;
    public function getFiles() : array;
    public function readAndWrite(bool $blocking, bool $close = \false) : array;
    public function areOpen() : bool;
    public function haveReadSupport() : bool;
    public function close();
}
