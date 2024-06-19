<?php

namespace e2ePhpParserVersion\Test;

use e2ePhpParserVersion\AstSampleProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AstSampleProvider::class)]
class AstSampleProviderTest extends TestCase
{
    public function test_it_can_provide_a_sample(): void
    {
        $sample = AstSampleProvider::provideSample();

        self::assertNotEmpty($sample);
    }
}
