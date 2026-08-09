# PHP version support policy

## Context

Supporting an older PHP version allows more projects to run the latest Infection release,
but incurs a maintenance cost. Each supported version expands the CI matrix, constrains
dependencies and development tools, and prevents the codebase from using newer language
features. As Infection is a development tool with many released versions, projects that
cannot upgrade PHP can continue to use an older compatible Infection release.

Infection originally supported PHP versions conservatively to encourage adoption. As the
project and its user base matured, [#1760] reconsidered this approach. The resulting upgrade
from PHP 7.4 to PHP 8.1 established that an ongoing security-support period alone was not
sufficient reason to retain support for a PHP version.

## Decision

We will maintain a progressive PHP support range. As a baseline, we review the oldest
supported version annually and normally raise the minimum by one PHP minor version. This
annual review provides a default cadence rather than an automatic deadline. Its timing, and
any decision to defer or accelerate an upgrade, depends on the following considerations:

- **PHP support status.** An end-of-life version should normally be dropped. A version in
  security-only support is a candidate for removal, especially when its remaining
  support period is short.
- **Ecosystem adoption.** We consider available Packagist usage statistics and wider PHP
  version trends. We may retain a widely used version, while low or sharply declining usage
  makes an increase less disruptive.
- **Language benefits.** The newer minimum must offer useful language or runtime features
  that materially benefit Infection. After changing the requirement, we may adopt
  those features in separate, focused changes.
- **Dependency and tooling support.** We prefer maintained versions of PHPUnit, static
  analysers, and other development dependencies. An older PHP version may be dropped when it
  blocks their upgrade or requires compatibility workarounds.
- **Maintenance and CI cost.** We consider the extra CI jobs, conditional code, dependency
  constraints, and contributor effort required by the oldest version.
- **Release timing and user impact.** A floor increase must be made deliberately in a release
  that communicates the new requirement. Dropping support for a PHP minor version requires
  a new Infection minor release; every patch release in an Infection minor line must retain
  the PHP versions supported by the first release in that line. For example, if Infection
  `0.34.0` supports PHP 8.3, every `0.34.x` release must continue to support it, and dropping
  PHP 8.3 requires an Infection `0.35.0` release. Users unable to upgrade PHP can remain on
  the latest compatible Infection release. This option reduces, but does not eliminate, the
  impact and must not replace an assessment of actual adoption.

These considerations inform a documented decision rather than a scoring system. A proposal
to raise the minimum must identify the versions being removed, their PHP support status and
adoption where data is available, the specific maintenance or language benefits, and the
migration path for affected users.

## Consequences

- Infection does not promise support for every PHP branch until that branch reaches end of
  life.
- Maintainers should expect one PHP minor version to leave the supported range annually,
  unless the annual review identifies a reason to vary that cadence.
- The latest Infection release may require a newer PHP version than some projects use to
  run their application or test suite.
- Maintainers can keep the codebase and its development dependencies on maintained versions
  and remove obsolete compatibility paths.
- A PHP floor increase is followed by separate refactoring where practical, so the requirement
  change and adoption of new syntax remain reviewable independently.

## Decision history

- In 2023, [#1760] and [#1765] dropped PHP 7.4 and PHP 8.0 in favour of PHP 8.1. PHP 7.4 was
  end-of-life, PHP 8.0 received security fixes only, PHP 8.1 already accounted for most of
  the reported Infection usage, and features such as constructor property promotion and
  first-class callables materially benefited the codebase.
- In 2024, [#2018] and [#2020] raised the minimum from PHP 8.1 to PHP 8.2 after consulting the
  PHP-supported-versions schedule. The code migration was performed separately with Rector.
- In 2026, [#3064] and [#3068] raised the minimum from PHP 8.2 to PHP 8.3. PHP 8.2 usage was
  understood to be declining sharply, and retaining it blocked the upgrade from PHPUnit 11,
  which was no longer receiving bug fixes, to PHPUnit 12. PHP 8.3 language features were
  adopted in subsequent changes.

## Status

Accepted.


[#1760]: https://github.com/infection/infection/issues/1760
[#1765]: https://github.com/infection/infection/pull/1765
[#2018]: https://github.com/infection/infection/pull/2018
[#2020]: https://github.com/infection/infection/pull/2020
[#3064]: https://github.com/infection/infection/issues/3064
[#3068]: https://github.com/infection/infection/pull/3068
