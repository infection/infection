<?php

namespace _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Contracts;

interface Signer
{
    public function sign($serializable);
    public function verify($signature);
}
