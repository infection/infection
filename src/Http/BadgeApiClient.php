<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Http;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class BadgeApiClient
{
    const STRYKER_DASHBOARD_API_URL = 'https://dashboard.stryker-mutator.io/api/reports';

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function sendReport(
        string $apiKey,
        string $repositorySlug,
        string $branch,
        float $mutationScore
    ) {
        $json = json_encode([
            'apiKey' => $apiKey,
            'repositorySlug' => $repositorySlug,
            'branch' => $branch,
            'mutationScore' => $mutationScore,
        ]);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::STRYKER_DASHBOARD_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);

        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln($response);
        }

        curl_close($ch);
    }
}
