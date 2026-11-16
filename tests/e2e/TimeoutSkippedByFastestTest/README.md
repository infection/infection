# Timeout skipped by the fastest test

* Related to https://github.com/infection/infection/issues/1965

A mutant is skipped only when even its fastest test (test class) is over the timeout, not when the
sum of all the tests covering it is:

* `add()` is covered by a fast test and two slow ones adding up over the timeout: the fast test kills the mutant.
* `isPositive()` has the same coverage, but no test kills the mutant: running all the tests is known to exceed
  the timeout, so it is reported as skipped, not as timed out.
* `decrement()` is covered only by tests that are each over the timeout: the mutant is skipped without being run.
