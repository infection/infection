<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Input;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\LogicException;
class InputArgument
{
    public const REQUIRED = 1;
    public const OPTIONAL = 2;
    public const IS_ARRAY = 4;
    private $name;
    private $mode;
    private $default;
    private $description;
    public function __construct(string $name, int $mode = null, string $description = '', $default = null)
    {
        if (null === $mode) {
            $mode = self::OPTIONAL;
        } elseif ($mode > 7 || $mode < 1) {
            throw new InvalidArgumentException(\sprintf('Argument mode "%s" is not valid.', $mode));
        }
        $this->name = $name;
        $this->mode = $mode;
        $this->description = $description;
        $this->setDefault($default);
    }
    public function getName()
    {
        return $this->name;
    }
    public function isRequired()
    {
        return self::REQUIRED === (self::REQUIRED & $this->mode);
    }
    public function isArray()
    {
        return self::IS_ARRAY === (self::IS_ARRAY & $this->mode);
    }
    public function setDefault($default = null)
    {
        if ($this->isRequired() && null !== $default) {
            throw new LogicException('Cannot set a default value except for InputArgument::OPTIONAL mode.');
        }
        if ($this->isArray()) {
            if (null === $default) {
                $default = [];
            } elseif (!\is_array($default)) {
                throw new LogicException('A default value for an array argument must be an array.');
            }
        }
        $this->default = $default;
    }
    public function getDefault()
    {
        return $this->default;
    }
    public function getDescription()
    {
        return $this->description;
    }
}
