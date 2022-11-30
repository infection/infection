<?php

namespace _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Signers;

use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Contracts\Signer;
class Hmac implements Signer
{
    protected $secret;
    public function __construct($secret)
    {
        $this->secret = $secret;
    }
    public function sign($serialized)
    {
        return ['serializable' => $serialized, 'hash' => \base64_encode(\hash_hmac('sha256', $serialized, $this->secret, \true))];
    }
    public function verify($signature)
    {
        return \hash_equals(\base64_encode(\hash_hmac('sha256', $signature['serializable'], $this->secret, \true)), $signature['hash']);
    }
}
