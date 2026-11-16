<?php

final class Paths
{
    public function __construct(
        /** @var list<string> Source paths. */
        public array $sourcePaths,
        /** @var list<string> */
        public array $testPaths,
    ) {
    }

    /** @param non-empty-string $path Existing type. */
    public function add(
        /** @var string This must not replace the existing type. */
        string $path,
    ): void {
    }
}

?>
-----
<?php

final class Paths
{
    /**
     * @param list<string> $sourcePaths Source paths.
     * @param list<string> $testPaths
     */
    public function __construct(
        public array $sourcePaths,
        public array $testPaths,
    ) {
    }

    /** @param non-empty-string $path Existing type. */
    public function add(
        string $path,
    ): void {
    }
}

?>
