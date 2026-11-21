# Releasing

DRange follows [Semantic Versioning](https://semver.org). The version is defined
**only by git tags** (`vMAJOR.MINOR.PATCH`); Packagist reads it from there. Do
not add a `version` field to `composer.json`.

## Choosing the version

- **MAJOR**: a backwards-incompatible change to the public API.
- **MINOR**: new functionality, or tightening that only rejects input that was
  already invalid.
- **PATCH**: backwards-compatible bug fixes only.

## Steps

1. On a branch, move the entries under `## [Unreleased]` in `CHANGELOG.md` into
   a new `## [X.Y.Z] - YYYY-MM-DD` section, and update the compare links at the
   bottom of the file.
2. Make sure `composer test` passes, then merge the branch into `master`.
3. Tag the merge commit and push the tag:

   ```bash
   git checkout master && git pull
   git tag -a vX.Y.Z -m "vX.Y.Z"
   git push origin vX.Y.Z
   ```

4. Create the GitHub release for the tag, using that version's
   `CHANGELOG.md` section as the release notes. If the `Release` workflow
   (`.github/workflows/release.yml`) is present, it does this automatically
   when the tag is pushed. Packagist picks up the new tag automatically.
