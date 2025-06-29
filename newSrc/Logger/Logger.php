<?php

namespace newSrc\Logger;

interface Logger
{
    public function logSkippingInitialTests(string $frameworkName): void;
}
