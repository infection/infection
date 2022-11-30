<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

require_once __DIR__ . '/root-version.php';
try {
    $composerRootVersion = get_composer_root_version(get_last_tag_name());
} catch (\RuntimeException $exception) {
    if (\false !== \getenv('CI') && \false === \getenv('PHP_SCOPER_GITHUB_TOKEN')) {
        echo 'Skipped to avoid saturating limit';
        return;
    }
    if (100 === $exception->getCode()) {
        return;
    }
    throw $exception;
}
\file_put_contents(__DIR__ . '/../.composer-root-version', \sprintf(<<<'BASH'
#!/usr/bin/env bash

export COMPOSER_ROOT_VERSION='%s'

BASH
, $composerRootVersion));
\file_put_contents($scrutinizerPath = __DIR__ . '/../.scrutinizer.yml', \preg_replace('/COMPOSER_ROOT_VERSION: \'.*?\'/', \sprintf('COMPOSER_ROOT_VERSION: \'%s\'', $composerRootVersion), \file_get_contents($scrutinizerPath)));
