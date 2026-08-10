---
name: handoff
description: Persist unfinished work in a self-contained brief so a fresh Codex session or another workspace can resume without re-deriving conversational context. Use when context has become too large or fragmented, work must move between sessions or sandboxes, or the user explicitly asks for a handoff.
---

# Handoff

Create a concise handoff for unfinished work. Preserve information that exists only in the
conversation; point to information already stored in the repository instead of copying it.

## Workflow

1. Inspect the current Git branch, working tree, and relevant on-disk artifacts. Do not
   modify, commit, discard, or clean working-tree changes while preparing the handoff.
2. If the user supplied a focus, make that the handoff's priority.
3. Write `var/agent/handoff.md`. Use a more specific filename under `var/agent/` when the
   user requests one or when preserving multiple handoffs. This repository ignores `var/`,
   so the brief remains local to the workspace.
4. Include only applicable sections:
   - **Goal** — the intended outcome.
   - **Current state** — completed, in-progress, and untouched work; include the branch and
     relevant commit or PR/MR identifiers when known.
   - **Context to preserve** — decisions and rationale, constraints, failed approaches,
     unresolved questions, and non-obvious risks that cannot be recovered from files.
   - **Next steps** — concrete actions in resume order, including useful verification.
   - **Suggested skills** — available skills that would materially help the next session,
     with when to use each. Omit this section when none apply.
   - **Pointers** — repository-relative paths, issue or PR/MR URLs, commands, and other
     artifacts containing the details.
5. Read the finished brief once for stale claims, ambiguity, and accidental secrets.
6. Tell the user where it was written and how the next session should resume: ask the fresh
   session to read that file, inspect the current working tree, and continue from it.

## Content Rules

- Keep the brief self-contained but terse. A new session should understand the goal and its
  next action without access to this conversation.
- Reference diffs, commits, specifications, ADRs, tickets, logs, and test output by path,
  identifier, or URL. Summarize them only when interpretation is needed for the next action.
- Record commands only when their exact form or result matters; distinguish commands already
  run from commands still to run.
- Clearly label assumptions, blockers, and unverified work.
- Never include credentials, tokens, private keys, personal data, or unnecessary environment
  details.
- Do not present a handoff as completion of the underlying task.
