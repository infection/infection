<?php

declare(strict_types=1);

namespace newSrc\MutationAnalyzer;

enum MutantExecutionStatus: int
{
    case COVERED = 0;
    case NOT_COVERED = 1;
    case SUSPICIOUS = 2;
}
