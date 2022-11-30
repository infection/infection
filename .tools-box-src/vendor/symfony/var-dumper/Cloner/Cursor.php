<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner;

class Cursor
{
    public const HASH_INDEXED = Stub::ARRAY_INDEXED;
    public const HASH_ASSOC = Stub::ARRAY_ASSOC;
    public const HASH_OBJECT = Stub::TYPE_OBJECT;
    public const HASH_RESOURCE = Stub::TYPE_RESOURCE;
    public $depth = 0;
    public $refIndex = 0;
    public $softRefTo = 0;
    public $softRefCount = 0;
    public $softRefHandle = 0;
    public $hardRefTo = 0;
    public $hardRefCount = 0;
    public $hardRefHandle = 0;
    public $hashType;
    public $hashKey;
    public $hashKeyIsBinary;
    public $hashIndex = 0;
    public $hashLength = 0;
    public $hashCut = 0;
    public $stop = \false;
    public $attr = [];
    public $skipChildren = \false;
}
