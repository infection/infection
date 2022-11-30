<?php

namespace _HumbugBoxb47773b41c19\Amp\Serialization;

final class JsonSerializer implements Serializer
{
    private const THROW_ON_ERROR = 4194304;
    private $associative;
    private $encodeOptions;
    private $decodeOptions;
    private $depth;
    public static function withAssociativeArrays(int $encodeOptions = 0, int $decodeOptions = 0, int $depth = 512) : self
    {
        return new self(\true, $encodeOptions, $decodeOptions, $depth);
    }
    public static function withObjects(int $encodeOptions = 0, int $decodeOptions = 0, int $depth = 512) : self
    {
        return new self(\false, $encodeOptions, $decodeOptions, $depth);
    }
    private function __construct(bool $associative, int $encodeOptions = 0, int $decodeOptions = 0, int $depth = 512)
    {
        $this->associative = $associative;
        $this->depth = $depth;
        $this->encodeOptions = $encodeOptions & ~self::THROW_ON_ERROR;
        $this->decodeOptions = $decodeOptions & ~self::THROW_ON_ERROR;
    }
    public function serialize($data) : string
    {
        $result = \json_encode($data, $this->encodeOptions, $this->depth);
        switch ($code = \json_last_error()) {
            case \JSON_ERROR_NONE:
                return $result;
            default:
                throw new SerializationException(\json_last_error_msg(), $code);
        }
    }
    public function unserialize(string $data)
    {
        $result = \json_decode($data, $this->associative, $this->depth, $this->decodeOptions);
        switch ($code = \json_last_error()) {
            case \JSON_ERROR_NONE:
                return $result;
            default:
                throw new SerializationException(\json_last_error_msg(), $code);
        }
    }
}
