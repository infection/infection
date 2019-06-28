<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Constraint\Constraint;

final class CountConstraint extends Constraint
{
    /**
     * @var int
     */
    private $count;

    public static function fromCount(int $count): self
    {
        $instance = new self;

        $instance->count = $count;

        return $instance;
    }

    public function matches($other): bool
    {
        return true;
    }

    public function toString(): string
    {
        return \sprintf(
            'is accepted by %s',
            self::class
        );
    }

    public function count(): int
    {
        return $this->count;
    }
}
