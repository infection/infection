<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

function get_last_tag_name() : string
{
    $responseContent = request_tags();
    echo '–––' . \PHP_EOL;
    echo $responseContent;
    echo \PHP_EOL;
    $lastRelease = parse_tag($responseContent);
    echo 'Latest tag found: ' . $lastRelease;
    echo \PHP_EOL;
    return $lastRelease;
}
function request_tags() : string
{
    $gitHubToken = \getenv('PHP_SCOPER_GITHUB_TOKEN');
    $headerOption = \false === $gitHubToken || '' === $gitHubToken ? '' : "-H \"Authorization: token {$gitHubToken}\"";
    $command = <<<BASH
curl -s {$headerOption} https://api.github.com/repos/humbug/php-scoper/tags?per_page=1
BASH;
    echo 'cURL command:' . \PHP_EOL;
    echo '$ ' . $command;
    echo \PHP_EOL;
    $responseContent = \shell_exec($command);
    if (null === $responseContent) {
        throw new \RuntimeException('Could not retrieve the last release endpoint.');
    }
    return $responseContent;
}
function parse_tag(string $responseContent) : string
{
    $decodedContent = \json_decode($responseContent, \false, 512, \JSON_PRETTY_PRINT & \JSON_THROW_ON_ERROR);
    if (!\is_array($decodedContent)) {
        throw new \RuntimeException(\sprintf('No tag name could be found in: %s', $responseContent), 100);
    }
    $lastReleaseInfo = \current($decodedContent);
    if (\false === $lastReleaseInfo) {
        throw new \RuntimeException(\sprintf('No tag name could be found in: %s', $responseContent), 100);
    }
    if (!$lastReleaseInfo->name || !\is_string($lastReleaseInfo->name)) {
        throw new \RuntimeException(\sprintf('No tag name could be found in: %s', $responseContent), 100);
    }
    $lastRelease = \trim($lastReleaseInfo->name);
    if ('' === $lastRelease) {
        throw new \RuntimeException('Invalid tag name found.');
    }
    return $lastRelease;
}
function get_composer_root_version(string $lastTagName) : string
{
    $tagParts = \explode('.', $lastTagName);
    \array_pop($tagParts);
    $tagParts[] = '99';
    return \implode('.', $tagParts);
}
