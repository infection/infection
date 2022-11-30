<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

require_once \dirname(__DIR__) . '/vendor/autoload.php';
$opts = \getopt('gdrpcv::', ['git-diff', 'diff', 'report', 'pdf', 'check::', 'verbose']);
if ($argc < 3) {
    die("Please pass two phars to diff them.\n");
}
$pharB = \array_pop($argv);
$pharA = \array_pop($argv);
$phars = [new \_HumbugBoxb47773b41c19\ParagonIE\Pharaoh\Pharaoh($pharA), new \_HumbugBoxb47773b41c19\ParagonIE\Pharaoh\Pharaoh($pharB)];
$diff = new \_HumbugBoxb47773b41c19\ParagonIE\Pharaoh\PharDiff($phars[0], $phars[1]);
if (!empty($opts['v']) || !empty($opts['verbose'])) {
    $diff->setVerbose(\true);
}
if (!empty($opts['c'])) {
    $return = $diff->listChecksums($opts['c']);
} elseif (!empty($opts['check'])) {
    $return = $diff->listChecksums($opts['check']);
} elseif (isset($opts['d']) || isset($opts['diff'])) {
    $return = $diff->printGnuDiff();
} else {
    $return = $diff->printGitDiff();
}
exit($return);
