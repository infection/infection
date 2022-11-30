<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Reflection;

final class Visibility
{
    public const PUBLIC = 'public';
    public const PROTECTED = 'protected';
    private function __construct(private string $variant)
    {
    }
    public static function asPublic() : self
    {
        return new self(self::PUBLIC);
    }
    public static function asProtected() : self
    {
        return new self(self::PROTECTED);
    }
    public function isPublic() : bool
    {
        return $this->variant === self::PUBLIC;
    }
    public function isProtected() : bool
    {
        return $this->variant === self::PROTECTED;
    }
}
