<?php declare(strict_types=1);
namespace Infection\Telemetry\Metric\Time;

use InvalidArgumentException;
use function max;
use function min;
use function round;
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

    public function toSeconds(): float
    {
        return $this->seconds + ($this->nanoseconds / 1_000_000_000);
    }



    /**
     * @return int<0, 100>
     */
    public function getPercentage(Duration $total): int
    {
        if ($total->isZero()) {
            return 0;
        }

        $currentSeconds = $this->toSeconds();
        $totalSeconds = $total->toSeconds();

        $percentage = (int) round(($currentSeconds / $totalSeconds) * 100.0);

        // TODO: is the min/max actually necessary here?
        return min(
            100,
            max(0, $percentage),
        );
    }

    private function isZero(): bool
    {
        return 0 === $this->seconds && 0 === $this->nanoseconds;
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
