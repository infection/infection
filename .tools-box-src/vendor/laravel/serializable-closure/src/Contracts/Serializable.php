<?php

namespace _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Contracts;

interface Serializable
{
    public function __invoke();
    public function getClosure();
}
