<?php

namespace _HumbugBoxb47773b41c19\Symfony\Contracts\Service\Attribute;

use _HumbugBoxb47773b41c19\Symfony\Contracts\Service\ServiceSubscriberTrait;
#[\Attribute(\Attribute::TARGET_METHOD)]
final class SubscribedService
{
    public function __construct(public ?string $key = null)
    {
    }
}
