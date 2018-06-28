<?php

namespace Namespace_;

class StringEnvelope
{
    const THIS_IS_FALSE = false;

    private $input;

    public function __construct($input)
    {
        $this->input = $input;
    }

    public function hasSubstring($substring): bool
    {
        return self::THIS_IS_FALSE !== $this->findPosition($substring);
    }

    public function hasNotSubstring($substring): bool
    {
        return self::THIS_IS_FALSE === $this->findPosition($substring);
    }

    private function findPosition($substring)
    {
        return strpos($this->input, $substring);
    }
}
