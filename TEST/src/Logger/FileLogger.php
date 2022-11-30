<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use function implode;
use function in_array;
use const PHP_EOL;
use _HumbugBox9658796bb9f0\Psr\Log\LoggerInterface;
use function _HumbugBox9658796bb9f0\Safe\file_put_contents;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_starts_with;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Exception\IOException;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
final class FileLogger implements MutationTestingResultsLogger
{
    public function __construct(private string $filePath, private Filesystem $fileSystem, private LineMutationTestingResultsLogger $lineLogger, private LoggerInterface $logger)
    {
    }
    public function log() : void
    {
        $content = implode(PHP_EOL, $this->lineLogger->getLogLines());
        if (str_starts_with($this->filePath, 'php://')) {
            if (in_array($this->filePath, ['php://stdout', 'php://stderr'], \true)) {
                file_put_contents($this->filePath, $content);
            } else {
                $this->logger->error(sprintf('<error>The only streams supported are "php://stdout" and "php://stderr"' . '. Got "%s"</error>', $this->filePath));
            }
            return;
        }
        try {
            $this->fileSystem->dumpFile($this->filePath, $content);
        } catch (IOException $exception) {
            $this->logger->error(sprintf('<error>%s</error>', $exception->getMessage()));
        }
    }
    public function getFilePath() : string
    {
        return $this->filePath;
    }
}
