# Bumping PHP version requirements

## Context

Historically we tried to support a wider range of PHP versions than necessary to
help out with the adoption of Infection within the community.

Since then, in [#1760], it was proposed to bump the minimum PHP requirement
from 7.4 to 8.1. 7.4 was EoL for a few days, 8.0 only on security support and
8.2 around the corner.

The question was to whether PHP 8.0 should still be supported and if the decision
to support a wider range of PHP version is still necessary.


## Decision

Since Infection is a lot more mature now in terms of feature with a good set of
versions published, having more progressive version requirements should no longer
hurt the adoption of the package.

In this peculiar case, since 8.1 offers quite a few more appealing features than
8.0, it has been decided that at the very least dropping support for a security-only
version is fine.


## Status

Accepted ([#1760]).


[#1760]: https://github.com/infection/infection/issues/1760
