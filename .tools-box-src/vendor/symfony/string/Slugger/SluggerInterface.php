<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\String\Slugger;

use _HumbugBoxb47773b41c19\Symfony\Component\String\AbstractUnicodeString;
interface SluggerInterface
{
    public function slug(string $string, string $separator = '-', string $locale = null) : AbstractUnicodeString;
}
