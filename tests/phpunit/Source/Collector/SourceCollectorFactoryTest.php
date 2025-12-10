<?php

namespace Infection\Tests\Source\Collector;

use Exception;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\SourceFilter\GitDiffFilter;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Configuration\SourceFilter\SourceFilter;
use Infection\Git\FakeGit;
use Infection\Git\Git;
use Infection\Source\Collector\BasicSourceCollector;
use Infection\Source\Collector\GitDiffSourceCollector;
use Infection\Source\Collector\SourceCollector;
use Infection\Source\Collector\SourceCollectorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceCollectorFactory::class)]
final class SourceCollectorFactoryTest extends TestCase
{
    /**
     * @param Exception|class-string<SourceCollector> $exceptionOrExpectedCollectorClassName
     */
    #[DataProvider('sourceFilterProvider')]
    public function test_it_can_create_a_collector(
        ?SourceFilter $sourceFilter,
        Exception|string $exceptionOrExpectedCollectorClassName,
    ): void
    {
        $factory = new SourceCollectorFactory(
            $this->createMock(Git::class),
        );

        if ($exceptionOrExpectedCollectorClassName instanceof Exception) {
            $this->expectExceptionObject($exceptionOrExpectedCollectorClassName);
        }

        $actual = $factory->create(
            '/path/to/project',
            new Source(['src', 'lib'], ['vendor', 'tests']),
            $sourceFilter,
        );

        if (!($exceptionOrExpectedCollectorClassName instanceof Exception)) {
            $this->assertSame($exceptionOrExpectedCollectorClassName, $actual::class);
        }
    }

    public static function sourceFilterProvider(): iterable
    {
        yield 'no filter' => [
            null,
            BasicSourceCollector::class,
        ];

        yield 'plain filter' => [
            new PlainFilter(['src/Service']),
            BasicSourceCollector::class,
        ];

        yield 'git diff filter' => [
            new GitDiffFilter('AM', '<merge-base-hash>'),
            GitDiffSourceCollector::class,
        ];
    }
}
