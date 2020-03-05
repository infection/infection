<?php

declare(strict_types=1);

namespace Infection\Tests\Env;

use Webmozart\Assert\Assert;
use function array_key_exists;
use function getenv;
use function Safe\putenv;
use function Safe\sprintf;

final class EnvBackup
{
    private $environmentVariables;

    /**
     * @param array<string, string> $environmentVariables
     */
    private function __construct(array $environmentVariables)
    {
        $this->environmentVariables = $environmentVariables;
    }

    public static function createSnapshot(): self
    {
        $environmentVariables = getenv();

        Assert::allString($environmentVariables);

        return new self($environmentVariables);
    }

    public function restore(): void
    {
        $snapshot = $this->environmentVariables;

        foreach (getenv() as $name => $value) {
            if (array_key_exists($name, $snapshot)) {
                $snapshotValue = $snapshot[$name];
                unset($snapshot[$name]);

                if ($snapshotValue === $value) {
                    continue;
                }

                putenv(sprintf('%s=%s', $name, $snapshotValue));

                continue;
            }

            putenv($name);
        }

        foreach ($snapshot as $name => $value) {
            putenv(sprintf('%s=%s', $name, $value));
        }
    }
}
