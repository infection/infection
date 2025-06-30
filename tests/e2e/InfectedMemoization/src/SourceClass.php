<?php

declare(strict_types=1);

namespace Infected;

class SourceClass
{
    private ?object $config = null;

    private function loadConfig(): object
    {
        return (object) ['key' => 'value'];
    }

    public function getConfig(): object
    {
        if (null !== $this->config) {
            return $this->config;
        }

        $this->config = $this->loadConfig();

        return $this->config;
    }
}
