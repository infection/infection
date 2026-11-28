# Releasing

Immutable releases are enabled: once published, a release's assets and tag cannot change.
CI therefore attaches the PHAR to a draft and publishes it itself. Never click **Publish**.

1. Releases → Draft a new release. Type the new tag (e.g. `0.35.13`), write the notes,
   **Save draft**.
2. Push the tag from the release branch:

   ```bash
   git checkout 0.35 && git pull
   git tag 0.35.13 && git push origin 0.35.13
   ```

3. The `Release` workflow builds and signs the PHAR, uploads it to the draft and publishes it.
   Without a draft, it publishes one with generated notes.

Never move or re-push a tag: Packagist picks it up as soon as it is pushed. If the workflow
fails, the draft stays unpublished. For a CI-only failure (e.g. flaky step, expired secret),
re-run the failed job. If the code needs a fix, release the next patch version and change
the draft's tag to it.
