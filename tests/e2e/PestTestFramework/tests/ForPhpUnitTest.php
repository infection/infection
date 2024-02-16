<?php

declare(strict_types=1);


namespace PestTestFramework\Test;


use PestTestFramework\ForPhpUnit;
use PHPUnit\Framework\TestCase;

final class ForPhpUnitTest extends TestCase
{
    public function test_it_returns_an_array(): void
    {
        $object = new ForPhpUnit();

        self::assertIsArray($object->getArray());
    }
}
