<?php

namespace Infection\DevTools\TestFramework\Coverage\Locator;

use Infection\TestFramework\Coverage\Locator\FixedLocator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FixedLocator::class)]
final class FixedLocatorTest extends TestCase
{
    public function test_it_can_be_instantiated_without_a_default_location(): void
    {
        $location = '/path/to/file';

        $locator = new FixedLocator($location);

        $this->assertSame($location, $locator->locate());
        $this->assertSame($location, $locator->getDefaultLocation());
    }

    public function test_it_can_be_instantiated_with_a_default_location(): void
    {
        $location = '/path/to/file';
        $defaultLocation = '/path/to/default-file';

        $locator = new FixedLocator($location, $defaultLocation);

        $this->assertSame($location, $locator->locate());
        $this->assertSame($defaultLocation, $locator->getDefaultLocation());
    }
}
