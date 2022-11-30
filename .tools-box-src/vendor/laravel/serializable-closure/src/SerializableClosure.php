<?php

namespace _HumbugBoxb47773b41c19\Laravel\SerializableClosure;

use Closure;
use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Exceptions\InvalidSignatureException;
use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Serializers\Signed;
use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Signers\Hmac;
class SerializableClosure
{
    protected $serializable;
    public function __construct(Closure $closure)
    {
        if (\PHP_VERSION_ID < 70400) {
            throw new PhpVersionNotSupportedException();
        }
        $this->serializable = Serializers\Signed::$signer ? new Serializers\Signed($closure) : new Serializers\Native($closure);
    }
    public function __invoke()
    {
        if (\PHP_VERSION_ID < 70400) {
            throw new PhpVersionNotSupportedException();
        }
        return \call_user_func_array($this->serializable, \func_get_args());
    }
    public function getClosure()
    {
        if (\PHP_VERSION_ID < 70400) {
            throw new PhpVersionNotSupportedException();
        }
        return $this->serializable->getClosure();
    }
    public static function setSecretKey($secret)
    {
        Serializers\Signed::$signer = $secret ? new Hmac($secret) : null;
    }
    public static function transformUseVariablesUsing($transformer)
    {
        Serializers\Native::$transformUseVariables = $transformer;
    }
    public static function resolveUseVariablesUsing($resolver)
    {
        Serializers\Native::$resolveUseVariables = $resolver;
    }
    public function __serialize()
    {
        return ['serializable' => $this->serializable];
    }
    public function __unserialize($data)
    {
        if (Signed::$signer && !$data['serializable'] instanceof Signed) {
            throw new InvalidSignatureException();
        }
        $this->serializable = $data['serializable'];
    }
}
