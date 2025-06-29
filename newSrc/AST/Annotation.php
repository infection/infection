<?php

declare(strict_types=1);

namespace newSrc\AST;

enum Annotation: int
{
    case ELIGIBLE = -1;
    case NOT_COVERED_BY_TESTS = 0;      // No test could be found in the provided traces.
    case IGNORED_WITH_ANNOTATION = 1;   // Was tagged by the user with @infection-ignore-all.
    case NOT_PART_OF_THE_GIT_DIFF = 3;  // The file is included in the git diff, but not this part of the code.
    case ARID_CODE = 2;                 // Maybe in the future we want different level of aridness.
}
