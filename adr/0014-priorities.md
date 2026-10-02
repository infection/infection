# Prioritise bug fixes, features, and only then enabling refactoring

## Context

Maintainers review Infection in their own time. Review capacity, not the number of PRs, sets how
much the project can absorb, and every pull request spends part of that capacity.

A large number of pull requests neither fix a reported bug nor add a requested feature. They
often restructure code, or add tooling that no current task needs.
Such a change is harder to review than a bug fix. A bug fix has a purpose: the
linked issue identifies a problem. A restructuring usually does not, so the reviewer has to work
out why it is proposed at all, and whether it brings any bug fix or feature closer. That answer
is frequently missing from the pull request, and the answer to this question is more important than the diff.
The result is that reported bugs and accepted features wait behind changes that no user asked
for.

## Decision

We sort review work on two axes: the type of change, and its urgency. They answer different
questions, and mixing them is what makes a queue unreadable. This increases the review time.

There are four types of change. The type decides whether we accept the work at all, and gives
the default order in which maintainers review it.

1. **Bug.** Incorrect mutants, wrong MSI, crashes, regressions, data loss, and performance
   regressions. Compatibility fixes for supported PHP, PHPUnit and test-framework versions
   belong here.
2. **Feature.** User-facing capability that an issue describes and the maintainers have accepted.
3. **Enabling Refactoring.** A structural change that **is a precondition for a specific fix or
   feature that is impractical to implement today**. The pull request must name and link that
   fix or feature.
4. **Other.** Everything else: developer tooling, style sweeps, renames, reorganisation, new
   static-analysis rules, and comparable work. We accept these only when they do not compete
   with work of types 1 to 3.

Urgency is the second independent axis. Two bug fixes are not equally
urgent: a crash or a wrong MSI in a common configuration comes before a rare edge case that has
a workaround. A maintainer may therefore review a very urgent item ahead of a whole type, and
say in the pull request why it goes first.

The two axes together give the review order:

|                      | Urgent  | High    | Medium  | Low     |
|----------------------|---------|---------|---------|---------|
| Bug                  | Now     | Next    | Queued  | Queued  |
| Feature              | Next    | Queued  | Queued  | Backlog |
| Enabling Refactoring | Queued  | Queued  | Backlog | Backlog |
| Other                | Backlog | Backlog | Backlog | Backlog |

- **Now** - we interrupt other work, and the fix goes out as soon as possible.
- **Next** - it goes to the top of its type, ahead of everything queued.
- **Queued** - normal order inside its type.
- **Backlog** - we review it when nothing above it competes for the same hours.

Two properties of this table are deliberate. The `Bug` row never falls below `Queued`, because
every reported bug earns a review even when it is minor. The `Other` row is flat, because urgency
reorders work but does not make a type 4 change into work that the project needs.

Priority determines which work receives attention first. It does not prescribe the same review
depth for every change. Review effort is proportional to the change's exposure: who it affects,
whether its correctness has objective boundaries, how easily it can be reverted, and who will
maintain it. A small change to mutation generation may need substantial review because an error
can silently affect users. A change confined to contributor tooling, guarded by PHPUnit or
static-analysis failures, or otherwise isolated from shipped behaviour may need much less.

Low-exposure work may therefore land opportunistically, including type 4 work, when its review
does not materially delay higher-priority work. Review capacity is not fully interchangeable:
work performed and maintained by someone with a particular need or area of expertise does not
necessarily displace a bug fix that requires another maintainer. "Does not compete" refers to
the implementation and review attention actually needed by higher-priority work, not merely to
the presence of such work in the queue.

Every pull request states its type, and the author is the one who states it. Type 3 also names
and links the issue that the refactoring unblocks, because the type alone does not tell the
reviewer what the change brings. A refactoring that cannot name that issue is type 4, and we
normally decline or postpone it because it is not a priority for maintainers' review. Urgency is for
maintainers to judge, not for the author to declare.

A rationale must be verifiable by the reviewer: a reproduced defect, a named capability, a
measured performance figure, or an existing project convention such as an ADR. Personal taste
is not a rationale. "I prefer this shape", "this is more idiomatic", and "this is cleaner"
do not justify changing code that has worked for years. This applies to maintainers as
much as to first-time contributors.

Infection is not a playground. Its users are other maintainers who trust its results, so a
change to working code answers to their needs, not to our curiosity about a technique. Try
ideas out in a fork, and bring back the ones that a bug or a feature needs.

At the same time, readability work is not inherently without value. Difficult code imposes a recurring
comprehension cost on every future change and a refactoring can transfer that repeated cost
into a single reviewed change. Because readability is subjective, the proposal must make that
cost verifiable, for example through repeated misunderstandings, defects caused by the current
structure, disproportionate effort in recent changes or a substantial and demonstrable
reduction in complexity. Without that evidence, or a specific bug or feature that the
refactoring enables, it remains type 4 work and retains the corresponding priority.

## Consequences

- Reported bugs and accepted features get reviewed first, and users get fixes sooner.
- Contributors learn early which type their work falls in, and lose less effort on changes
  that we will not merge.
- Refactoring keeps a place in the project, but must earn it by unblocking user-visible work.
- Maintainers may close a type 4 pull request without a detailed review, and must say why.
  Closing judges the type of change, not the contributor or the quality of the code.
- An urgent bug fix can go out before a queue of older bug fixes, because the recorded urgency
  puts it there.
- Some genuinely good cleanup will not be merged, or will wait a long time. We accept that
  cost, because the alternative spends review capacity that bug fixes need.

## Enforcement

Review only. Nothing here is automated, because no tool can decide whether a change is required.

The author states the type. A maintainer confirms it before an in-depth review, and corrects it
when the author has it wrong, which is expected for `Enabling Refactoring`. Urgency is set by a
maintainer.

Both values are recorded where every maintainer can see them, so that the order in the table is
visible instead of remembered, and so that the queue can be read in one place. The record covers
issues as well as pull requests, because a proposal that waits for a decision, an RFC for
example, spends the same review capacity as a diff.

## Alternatives considered

Reviewing every well-written pull request on its merits alone was rejected. It ignores the
constraint that causes the problem, which is finite review capacity, and in practice it lets
optional work displace required work.

Refusing all refactoring was rejected. Parts of the codebase do block new features, and
forbidding structural change would push contributors into workarounds that cost more later.

Triaging the queue instead of publishing a priority order was rejected, though not because
triage is wrong: the Enforcement section is triage. Triage alone is not enough because it acts
only after the work exists. The contributor has already spent their time, a maintainer still
has to open every pull request to sort it, and the sorting is itself review work. Without a
written criterion, each triage decision also reads as the preference of whoever performed it,
which is the disagreement this ADR exists to prevent. The priority order gives triage a
criterion, and gives contributors that criterion before they open an editor.

## Status

Proposed.
