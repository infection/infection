<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework;

use Infection\TestFramework\Coverage\CoverageReport;
use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\SourceFileData;
use function Pipeline\take;

final class LegacyXmlCoverageParserAdapter
{
    private $parser;

    public function __construct(IndexXmlCoverageParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return array<string, CoverageReport>
     */
    public function parse(string $coverageIndexPath, string $xmlIndexCoverageContent): array
    {
        $coverage = take($this->parser->parse($coverageIndexPath, $xmlIndexCoverageContent))
            ->map(static function (SourceFileData $data) {
                yield $data->getSplFileInfo()->getRealPath() => $data->retrieveCoverageReport();
            });

        return iterator_to_array($coverage, true);
    }

}