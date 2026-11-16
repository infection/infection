# PHPUnit 13 with a custom configuration directory and no source coverage

This scenario was first added as `PHPUnit_Custom_Config_Dir`, later renamed
`PHPUnit9_Custom_Config_Dir`. This version uses PHPUnit 13 without source
coverage configured.

The PHPUnit configuration lives in `build/phpunit.xml`. Infection configures two
source directories in a single run:

- Relative `src`, which must resolve against the Infection configuration directory
  rather than PHPUnit's `build/` directory. The generated Infection configuration
  lives in a temporary directory with a `src` link to the fixture's source.
- An absolute path containing a symbolic link followed by `../extra-src`. The
  link points to `build/`, so the filesystem resolves the path to the fixture's
  `extra-src/`. Canonicalization would instead select the nonexistent `extra-src/`
  inside the temporary directory.

Each directory contains a tested class. The expected summary requires two killed
mutants, so losing coverage for either directory fails the scenario.

The script explicitly configures `pcov.directory` to include the scenario. PCOV's
automatic directory selection canonicalizes paths independently of PHPUnit's
coverage configuration.
