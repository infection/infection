<?php
/**
 * Created by PhpStorm.
 * User: fenikkusu
 * Date: 8/25/18
 * Time: 1:00 PM
 */

namespace Infection\TestFramework\Config\Builder\Initial;

use Infection\Config\InfectionConfig;
use Infection\TestFramework\Config\Builder\AbstractBuilder as Builder;

/**
 * @internal
 */
abstract class AbstractBuilder extends Builder implements BuilderInterface
{
    /** @var bool */
    private $skipCoverage;

    public function __construct(
        InfectionConfig $infectionConfig,
        string $tempDirectory,
        string $configPath,
        bool $skipCoverage
    )
    {
        $this->skipCoverage = $skipCoverage;

        parent::__construct($infectionConfig, $tempDirectory, $configPath);
    }

    protected function canSkipCoverage(): bool
    {
        return $this->skipCoverage;
    }
}
