<?php

declare(strict_types=1);

namespace Infection\Logger\MutationAnalysis\TeamCity;

use Symfony\Component\Filesystem\Path;
use function Later\now;

/**
 * @phpstan-import-type MessageAttributes from TeamCity
 *
 * @internal
 */
final readonly class TestSuite
{
    /**
     * @param string $location Absolute path of the source file.
     */
    public function __construct(
        public string $location,
        public string $name,
        public string $flowId,
    ) {
    }

    public static function create(
        string $sourceFilePath,
        string $basePath,
    ): self
    {
        $relativeSourceFilePath = Path::makeRelative(
            $sourceFilePath,
            $basePath,
        );

        return new self(
            location: $sourceFilePath,
            name: $relativeSourceFilePath,
            flowId: FlowIdFactory::create($relativeSourceFilePath),
        );
    }

    /**
     * @return MessageAttributes
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'nodeId' => $this->flowId,
            'parentNodeId' => '0',
            'locationHint' => sprintf(
                'file://%s',
                $this->location,
            ),
        ];
    }
}
