<?php declare(strict_types=1);
namespace Infection\Telemetry\Metric\Time;

use InvalidArgumentException;
use function sprintf;

final readonly class Duration
{
    private const NANOSECONDS_MAX = 10e9;

    /**
     * @param positive-int|0 $seconds
     * @param int<0, 999999999> $nanoseconds
     */
    public static function fromSecondsAndNanoseconds(int $seconds, int $nanoseconds): self
    {
        return new self(
            $seconds,
            $nanoseconds,
        );
    }

    /**
     * @param positive-int|0 $seconds
     * @param int<0, 999999999> $nanoseconds
     */
    private function __construct(
        public int $seconds,
        public int $nanoseconds,
    )
    {
        self::assertIsANatural($seconds, 'seconds');
        self::assertIsANatural($nanoseconds, 'nanoseconds');
        self::assertIsValidNanoSeconds($nanoseconds);
    }

    public function toFloat(): float
    {
        $seconds     = $this->seconds - $start->seconds;
        $nanoseconds = $this->nanoseconds - $start->nanoseconds;

        if ($nanoseconds < 0) {
            $seconds--;

            $nanoseconds += self::NANOSECONDS_MAX;
        }

        if ($seconds < 0) {
            return Duration::fromSecondsAndNanoseconds(0, 0);
        }

        return Duration::fromSecondsAndNanoseconds(
            $seconds,
            $nanoseconds,
        );
    }

    /**
     * @psalm-assert positive-int|0 $value
     */
    private static function assertIsANatural(int $value, string $type): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'Value for %s must not be negative.',
                    $type,
                ),
            );
        }
    }

    private static function assertIsValidNanoSeconds(int $nanoseconds): void
    {
        if ($nanoseconds >= self::NANOSECONDS_MAX) {
            throw new InvalidArgumentException(
                sprintf(
                    'Value for nanoseconds must not be greater or equal than %s.',
                    self::NANOSECONDS_MAX,
                ),
            );
        }
    }
}
