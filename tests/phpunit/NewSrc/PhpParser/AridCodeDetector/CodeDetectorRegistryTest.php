<?php

namespace Infection\Tests\NewSrc\PhpParser\AridCodeDetector;

use newSrc\AST\AridCodeDetector\AridCodeDetector;
use newSrc\AST\AridCodeDetector\CodeDetectorRegistry;
use PhpParser\Node\Stmt\Echo_;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodeDetectorRegistry::class)]
final class CodeDetectorRegistryTest extends TestCase
{
    public function test_it_returns_false_when_no_detectors_are_registered(): void
    {
        $registry = new CodeDetectorRegistry();
        $node = new Echo_([]);

        $result = $registry->isArid($node);

        $this->assertFalse($result);
    }

    public function test_it_returns_false_when_all_detectors_return_false(): void
    {
        $detector1 = $this->createMock(AridCodeDetector::class);
        $detector2 = $this->createMock(AridCodeDetector::class);

        $node = new Echo_([]);

        $detector1
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(false);

        $detector2
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(false);

        $registry = new CodeDetectorRegistry(
            $detector1,
            $detector2,
        );

        $result = $registry->isArid($node);

        $this->assertFalse($result);
    }

    public function test_it_returns_true_when_any_detector_returns_true(): void
    {
        $detector1 = $this->createMock(AridCodeDetector::class);
        $detector2 = $this->createMock(AridCodeDetector::class);

        $node = new Echo_([]);

        $detector1
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(false);

        $detector2
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(true);

        $registry = new CodeDetectorRegistry(
            $detector1,
            $detector2,
        );

        $result = $registry->isArid($node);

        $this->assertTrue($result);
    }

    public function test_it_stops_at_the_first_detector_returning_true(): void
    {
        $detector1 = $this->createMock(AridCodeDetector::class);
        $detector2 = $this->createMock(AridCodeDetector::class);

        $node = new Echo_([]);

        $detector1
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(true);

        $detector2
            ->expects($this->never())
            ->method('isArid');

        $registry = new CodeDetectorRegistry(
            $detector1,
            $detector2,
        );

        $result = $registry->isArid($node);

        $this->assertTrue($result);
    }
}
