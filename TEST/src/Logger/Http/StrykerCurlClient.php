<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger\Http;

use const CURLINFO_HTTP_CODE;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_URL;
use function _HumbugBox9658796bb9f0\Safe\curl_exec;
use function _HumbugBox9658796bb9f0\Safe\curl_getinfo;
use function _HumbugBox9658796bb9f0\Safe\curl_init;
use function _HumbugBox9658796bb9f0\Safe\curl_setopt;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
class StrykerCurlClient
{
    private const STRYKER_DASHBOARD_API_BASE_URL = 'https://dashboard.stryker-mutator.io/api/reports';
    public function request(string $repositorySlug, string $version, string $apiKey, string $reportJson) : Response
    {
        $url = sprintf('%s/%s/%s', self::STRYKER_DASHBOARD_API_BASE_URL, $repositorySlug, $version);
        $headers = ['Content-Type: application/json', 'Host: dashboard.stryker-mutator.io', sprintf('X-Api-Key: %s', $apiKey)];
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, \true);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $reportJson);
        curl_setopt($handle, CURLOPT_HEADER, \true);
        $body = (string) curl_exec($handle);
        $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        return new Response($statusCode, $body);
    }
}
