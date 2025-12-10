<?php

namespace Infection\Tests\Source\Collector\GitDiffSourceCollector;

use Infection\Source\Collector\GitDiffSourceCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitDiffSourceCollector::class)]
final class GitDiffSourceCollectorTest extends TestCase
{
    // TODO: quite annoying to do right now due to the hard dependency on BasicSourceCollector.
}
