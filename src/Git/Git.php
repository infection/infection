<?php

namespace Infection\Git;

interface Git
{
    public function getDefaultBase(): string;

    public function getDefaultBaseFilter(): string;
}
