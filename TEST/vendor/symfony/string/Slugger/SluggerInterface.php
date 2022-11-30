<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\String\Slugger;

use _HumbugBox9658796bb9f0\Symfony\Component\String\AbstractUnicodeString;
interface SluggerInterface
{
    public function slug(string $string, string $separator = '-', string $locale = null) : AbstractUnicodeString;
}
