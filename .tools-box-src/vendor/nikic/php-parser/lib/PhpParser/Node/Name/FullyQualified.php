<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Name;

class FullyQualified extends \_HumbugBoxb47773b41c19\PhpParser\Node\Name
{
    public function isUnqualified() : bool
    {
        return \false;
    }
    public function isQualified() : bool
    {
        return \false;
    }
    public function isFullyQualified() : bool
    {
        return \true;
    }
    public function isRelative() : bool
    {
        return \false;
    }
    public function toCodeString() : string
    {
        return '\\' . $this->toString();
    }
    public function getType() : string
    {
        return 'Name_FullyQualified';
    }
}
