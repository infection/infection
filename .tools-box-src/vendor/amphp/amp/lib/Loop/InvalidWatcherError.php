<?php

namespace _HumbugBoxb47773b41c19\Amp\Loop;

class InvalidWatcherError extends \Error
{
    private $watcherId;
    public function __construct(string $watcherId, string $message)
    {
        $this->watcherId = $watcherId;
        parent::__construct($message);
    }
    public function getWatcherId()
    {
        return $this->watcherId;
    }
}
