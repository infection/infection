<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger\Http;

use function in_array;
use _HumbugBox9658796bb9f0\Psr\Log\LoggerInterface;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
class StrykerDashboardClient
{
    public function __construct(private StrykerCurlClient $client, private LoggerInterface $logger)
    {
    }
    public function sendReport(string $repositorySlug, string $branch, string $apiKey, string $reportJson) : void
    {
        $response = $this->client->request($repositorySlug, $branch, $apiKey, $reportJson);
        $statusCode = $response->getStatusCode();
        if (!in_array($statusCode, [Response::HTTP_OK, Response::HTTP_CREATED], \true)) {
            $this->logger->warning(sprintf('Stryker dashboard returned an unexpected response code: %s', $statusCode));
        }
        $this->logger->notice(sprintf('Dashboard response:%s%s', "\r\n", $response->getBody()));
    }
}
