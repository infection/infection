<?php

declare(strict_types=1);

namespace newSrc\MutationAnalyzer;

final class Mutant
{
    // TODO: lazily evaluated, dumps the mutation on the FS and other info
    //  shouldn't dump on the FS right away, e.g. for a dry-run we could use only the diff but not needed to dump to the FS
}
