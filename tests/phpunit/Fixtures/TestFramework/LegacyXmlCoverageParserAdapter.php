<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework;


use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\CoveredFileData;
use function Pipeline\take;

final class LegacyXmlCoverageParserAdapter
{
    private $parser;

    public function __construct(IndexXmlCoverageParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return array<string, CoverageFileData>
     */
    public function parse(string $coverageXmlContent): array
    {
        $coverage = take($this->parser->parse($coverageXmlContent))
            ->map(static function (CoveredFileData $data) {
                yield $data->getSplFileInfo()->getRealPath() => $data->retrieveCoverageFileData();
            });

        return iterator_to_array($coverage, true);
    }

}