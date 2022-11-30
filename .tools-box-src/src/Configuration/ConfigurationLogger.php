<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Configuration;

use function array_keys;
use function trim;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class ConfigurationLogger
{
    private array $recommendations = [];
    private array $warnings = [];
    public function addRecommendation(string $message) : void
    {
        $message = trim($message);
        Assert::false('' === $message, 'Expected to have a message but a blank string was given instead.');
        $this->recommendations[$message] = \true;
    }
    public function getRecommendations() : array
    {
        return array_keys($this->recommendations);
    }
    public function addWarning(string $message) : void
    {
        $message = trim($message);
        Assert::false('' === $message, 'Expected to have a message but a blank string was given instead.');
        $this->warnings[$message] = \true;
    }
    public function getWarnings() : array
    {
        return array_keys($this->warnings);
    }
}
