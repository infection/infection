<?php

namespace _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Serializers;

use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Contracts\Serializable;
use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Exceptions\InvalidSignatureException;
use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Exceptions\MissingSecretKeyException;
class Signed implements Serializable
{
    public static $signer;
    protected $closure;
    public function __construct($closure)
    {
        $this->closure = $closure;
    }
    public function __invoke()
    {
        return \call_user_func_array($this->closure, \func_get_args());
    }
    public function getClosure()
    {
        return $this->closure;
    }
    public function __serialize()
    {
        if (!static::$signer) {
            throw new MissingSecretKeyException();
        }
        return static::$signer->sign(\serialize(new Native($this->closure)));
    }
    public function __unserialize($signature)
    {
        if (static::$signer && !static::$signer->verify($signature)) {
            throw new InvalidSignatureException();
        }
        $serializable = \unserialize($signature['serializable']);
        $this->closure = $serializable->getClosure();
    }
}
