Run Infection **without** git. This ensures it works and doesn't fail because of git absence.

- Infection starts out `git` as a bare argv command without a full path so the executable is resolved through PATH.
- If we point PATH at an empty directory then we will create an environment without git in PATH.
- But we can't just unset or blank PATH because it will then reset to glibc's default of `/bin:/usr/bin` and `git` is there exactly.

Covers https://github.com/infection/infection/pull/1981 and https://github.com/infection/infection/pull/1982
