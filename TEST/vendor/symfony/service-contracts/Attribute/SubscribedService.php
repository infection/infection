<?php

namespace _HumbugBox9658796bb9f0\Symfony\Contracts\Service\Attribute;

use _HumbugBox9658796bb9f0\Symfony\Contracts\Service\ServiceSubscriberTrait;
#[\Attribute(\Attribute::TARGET_METHOD)]
final class SubscribedService
{
    public function __construct(public ?string $key = null)
    {
    }
}
