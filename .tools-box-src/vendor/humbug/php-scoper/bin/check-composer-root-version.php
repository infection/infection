<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

require_once __DIR__ . '/root-version.php';
try {
    $expectedComposerRootVersion = get_composer_root_version(get_last_tag_name());
} catch (\RuntimeException $exception) {
    if (\false !== \getenv('TRAVIS') && \false === \getenv('GITHUB_TOKEN')) {
        return;
    }
    if (100 === $exception->getCode()) {
        return;
    }
    throw $exception;
}
\preg_match('/COMPOSER_ROOT_VERSION=\'(?<version>.*?)\'/', \file_get_contents(__DIR__ . '/../.composer-root-version'), $matches);
$currentRootVersion = $matches['version'];
if ($expectedComposerRootVersion !== $currentRootVersion) {
    \file_put_contents('php://stderr', \sprintf('Expected the COMPOSER_ROOT_VERSION value to be "%s" but got "%s" instead.' . \PHP_EOL, $expectedComposerRootVersion, $currentRootVersion));
    exit(1);
}
