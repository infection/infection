<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

final class SourceMethodLineRange
{
    public function __construct(private int $startLine, private int $endLine)
    {
    }
    public function getStartLine() : int
    {
        return $this->startLine;
    }
    public function getEndLine() : int
    {
        return $this->endLine;
    }
}
