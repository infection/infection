<?php

namespace _HumbugBoxb47773b41c19\Amp;

trait Struct
{
    private $__propertySuggestThreshold = 70;
    /**
    @psalm-return
    */
    public function __get(string $property)
    {
        throw new \Error($this->generateStructPropertyError($property));
    }
    /**
    @psalm-return
    */
    public function __set(string $property, $value)
    {
        throw new \Error($this->generateStructPropertyError($property));
    }
    private function generateStructPropertyError(string $property) : string
    {
        $suggestion = $this->suggestPropertyName($property);
        $suggestStr = $suggestion == "" ? "" : " ... did you mean \"{$suggestion}?\"";
        return \sprintf("%s property \"%s\" does not exist%s", \str_replace("\x00", "@", \get_class($this)), $property, $suggestStr);
    }
    private function suggestPropertyName(string $badProperty) : string
    {
        $badProperty = \strtolower($badProperty);
        $bestMatch = "";
        $bestMatchPercentage = 0;
        /**
        @psalm-suppress */
        foreach ($this as $property => $value) {
            if ($property[0] === "_") {
                continue;
            }
            \similar_text($badProperty, \strtolower($property), $byRefPercentage);
            if ($byRefPercentage > $bestMatchPercentage) {
                $bestMatchPercentage = $byRefPercentage;
                $bestMatch = $property;
            }
        }
        return $bestMatchPercentage >= $this->__propertySuggestThreshold ? $bestMatch : "";
    }
}
