<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

class ImgStub extends ConstStub
{
    public function __construct(string $data, string $contentType, string $size = '')
    {
        $this->value = '';
        $this->attr['img-data'] = $data;
        $this->attr['img-size'] = $size;
        $this->attr['content-type'] = $contentType;
    }
}
