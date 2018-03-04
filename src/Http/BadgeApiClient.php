<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Http;

class BadgeApiClient
{
    const STRYKER_DASHBOARD_API_URL = 'https://dashboard.stryker-mutator.io/api/reports';

    public function sendReport(
        string $apiKey,
        string $repositorySlug,
        string $branch,
        float $mutationScore)
    {
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

        curl_exec($ch);

        curl_close($ch);
    }
}
