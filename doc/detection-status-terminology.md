# Detection Status Terminology

This note records terminology research for a later addition to the canonical
[nomenclature](nomenclature.md). It does not establish a project-wide term yet.

## Findings

The mutation testing literature does not appear to prescribe a single collective term for
the possible results of analysing a mutant. It primarily describes mutants individually as
*killed* (or *dead*), *live* (or *surviving*), *equivalent*, invalid, or uncovered. A test
*detects* or *distinguishes* a mutant when the test behaves differently for the mutant and
the original program.

Mutation testing tools use several names for the broader classification:

- [PIT uses `DetectionStatus`][pit-detection-status] for `KILLED`, `SURVIVED`,
  `TIMED_OUT`, `NO_COVERAGE`, `RUN_ERROR`, and other states. PIT gives every state an
  `isDetected()` value, so detection is a property derived from the broader status. This is
  the closest precedent for Infection's current name and model.
- [Mutation Testing Elements uses `MutantStatus`][mutation-testing-elements-mutant-status]
  for `Killed`, `Survived`, `NoCoverage`, `Timeout`, `CompileError`, `RuntimeError`,
  `Ignored`, and `Pending`. Stryker and Infection's Stryker HTML report use this
  interoperable report model.
- Tools and report formats commonly expose an unqualified `status` on a mutation or mutant
  result. `Outcome` also appears in implementations, but is not the dominant interchange
  term.

`DetectionStatus` is therefore established terminology rather than an Infection-specific
invention. However, it is narrower than the values currently represented by Infection's
enum: `IGNORED`, `SKIPPED`, and `NOT_COVERED` do not themselves answer whether a mutant was
detected unless Infection defines a detection semantic for every case, as PIT does.

`MutantStatus` is the strongest candidate for the complete classification because it is
neutral about whether evaluation occurred and aligns with Mutation Testing Elements.
`MutantAnalysisOutcome` is more explicit, but has less precedent. `MutationStatus` is less
suitable in Infection because the project deliberately distinguishes a serializable
mutation from its materialised mutant.

## Follow-up

Before adding the term to `doc/nomenclature.md`, decide whether Infection models:

1. one exhaustive mutant status from which detection is derived; or
2. a narrower detection result, separate from skipped, ignored, uncovered, and failed
   evaluation outcomes.

If the model remains one exhaustive classification, prefer `MutantStatus`, or retain
`DetectionStatus` with an explicit definition of the detection semantics for every case.

[pit-detection-status]: https://github.com/hcoles/pitest/blob/master/pitest/src/main/java/org/pitest/mutationtest/DetectionStatus.java
[mutation-testing-elements-mutant-status]: https://github.com/stryker-mutator/mutation-testing-elements/blob/master/packages/metrics-scala/metrics/src/main/scala/mutationtesting/MutantStatus.scala
