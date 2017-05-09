<?php

declare(strict_types=1);

namespace Infection\Utils;


class InfectionConfig
{
    const PROCESS_TIMEOUT_SECONDS = 10;
    const DEFAULT_SOURCE_DIRS = ['.'];
    const DEFAULT_EXCLUDE_DIRS = ['vendor'];

    /**
     * @var \stdClass
     */
    private $config;

    public function __construct(\stdClass $config)
    {
        $this->config = $config;
    }

    public function getPhpUnitConfigDir(): string
    {
        if (isset($this->config->phpUnit->configDir)) {
            return getcwd() . DIRECTORY_SEPARATOR . $this->config->phpUnit->configDir;
        }

        return getcwd();
    }

    public function getProcessTimeout(): int
    {
        return $this->config->timeout ?? self::PROCESS_TIMEOUT_SECONDS;
    }

    public function getSourceDirs(): array
    {
        return $this->config->source->directories ?? self::DEFAULT_SOURCE_DIRS;
    }

    public function getSourceExcludeDirs(): array
    {
      if (isset($this->config->source->exclude) && is_array($this->config->source->exclude)) {
            $originalExcludedDirs = $this->config->source->exclude;
            $excludedDirs = [];

            foreach ($originalExcludedDirs as $originalExcludedDir) {
                if (strpos($originalExcludedDir, '*') === false) {
                    $excludedDirs[] = $originalExcludedDir;
                } else {
                    $excludedDirs = array_merge(
                        $excludedDirs,
                        $this->getExcludedDirsByPattern($originalExcludedDir)
                    );
                }
            }

            return $excludedDirs;
        }

        return self::DEFAULT_EXCLUDE_DIRS;
    }

    private function getExcludedDirsByPattern(string $originalExcludedDir)
    {
        $excludedDirs = [];
        $srcDirs = $this->getSourceDirs();

        foreach ($srcDirs as $srcDir) {
            $unpackedPaths = glob(
              sprintf('%s/%s', $srcDir, $originalExcludedDir),
              GLOB_ONLYDIR
            );

            if ($unpackedPaths) {
                $excludedDirs = array_merge(
                    $excludedDirs,
                    array_map(
                      function ($excludeDir) use ($srcDir) {
                          return ltrim(
                              substr_replace($excludeDir, '', 0, strlen($srcDir)),
                              DIRECTORY_SEPARATOR
                          );
                      },
                      $unpackedPaths
                    )
                );
            }
        }

        return $excludedDirs;
    }
}
