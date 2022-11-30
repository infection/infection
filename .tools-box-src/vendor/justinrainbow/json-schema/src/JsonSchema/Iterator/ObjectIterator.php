<?php

namespace _HumbugBoxb47773b41c19\JsonSchema\Iterator;

class ObjectIterator implements \Iterator, \Countable
{
    private $object;
    private $position = 0;
    private $data = array();
    private $initialized = \false;
    public function __construct($object)
    {
        $this->object = $object;
    }
    public function current()
    {
        $this->initialize();
        return $this->data[$this->position];
    }
    public function next()
    {
        $this->initialize();
        $this->position++;
    }
    public function key()
    {
        $this->initialize();
        return $this->position;
    }
    public function valid()
    {
        $this->initialize();
        return isset($this->data[$this->position]);
    }
    public function rewind()
    {
        $this->initialize();
        $this->position = 0;
    }
    public function count()
    {
        $this->initialize();
        return \count($this->data);
    }
    private function initialize()
    {
        if (!$this->initialized) {
            $this->data = $this->buildDataFromObject($this->object);
            $this->initialized = \true;
        }
    }
    private function buildDataFromObject($object)
    {
        $result = array();
        $stack = new \SplStack();
        $stack->push($object);
        while (!$stack->isEmpty()) {
            $current = $stack->pop();
            if (\is_object($current)) {
                \array_push($result, $current);
            }
            foreach ($this->getDataFromItem($current) as $propertyName => $propertyValue) {
                if (\is_object($propertyValue) || \is_array($propertyValue)) {
                    $stack->push($propertyValue);
                }
            }
        }
        return $result;
    }
    private function getDataFromItem($item)
    {
        if (!\is_object($item) && !\is_array($item)) {
            return array();
        }
        return \is_object($item) ? \get_object_vars($item) : $item;
    }
}
