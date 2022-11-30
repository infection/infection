<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger\Http;

use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class Response
{
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    private int $statusCode;
    public function __construct(int $statusCode, private string $body)
    {
        Assert::range($statusCode, 200, 599, 'Expected an HTTP status code. Got "%s"');
        $this->statusCode = $statusCode;
    }
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }
    public function getBody() : string
    {
        return $this->body;
    }
}
