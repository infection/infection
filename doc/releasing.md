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

If the workflow fails, the draft stays unpublished: fix the problem, then re-push the tag
(`git push --delete origin 0.35.13 && git tag -d 0.35.13`, then step 2). A published release
cannot be fixed; release the next patch version instead.
