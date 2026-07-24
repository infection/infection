<?php

declare(strict_types=1);

use Infection\FileSystem\FileSystem;
use Infection\Tests\AutoReview\AI\AdrIndexGenerator;
use Infection\Tests\AutoReview\AI\AdrIndexUpdater;

require_once __DIR__.'/../vendor/autoload.php';

$checkOnly = match ($argc) {
    1 => false,
    2 => '--check' === $argv[1],
    default => null,
};

if (null === $checkOnly) {
    fwrite(STDERR, "Usage: php devTools/update-adr-index.php [--check]\n");

    exit(2);
}

$updater = new AdrIndexUpdater(
    new FileSystem(),
    new AdrIndexGenerator(),
);

$updater->update(__DIR__.'/..', $checkOnly);

fwrite(STDOUT, "Done.\n");
