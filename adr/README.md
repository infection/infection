# Architecture Decision Records

Architecture Decision Records (ADRs) document durable project decisions and
the context that led to them. They allow maintainers, contributors, and tools to
understand not only the current convention, but why it was chosen.

## When to create an ADR

Create an ADR when a decision has lasting consequences for the architecture,
public API, dependencies, testing strategy, or project-wide conventions. An ADR
is especially useful when reasonable alternatives exist or the same question is
likely to recur during reviews.

Do not use an ADR for implementation details, contribution workflows, or a
description of the code as it happens to exist. Those belong in code comments,
contributor documentation, or architecture documentation.

## Naming

Name new records `NNNN-short-kebab-case-title.md`, where `NNNN` is the next
unused four-digit number. For example:

```text
0007-declare-phpunit-coverage-metadata.md
```

Some older records predate this naming convention and retain their historical
filenames.

## Creating an ADR

Copy [`0000-template.md`](0000-template.md), assign the next number, and replace
the guidance comments with the proposed decision. Keep the template's section
order and leave `Status` as the final section. New proposals start with the
`Proposed` status and become `Accepted` when the decision is approved.
