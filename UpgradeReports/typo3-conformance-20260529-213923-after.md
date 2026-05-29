# TYPO3 conformance report - 2026-05-29 21:39:23

## Result

- TYPO3 14-only package metadata is in `composer.json`.
- `ext_emconf.php` was removed for TYPO3 14 packaging.
- Existing site sets under `Configuration/Sets/*` are used by local site configuration.
- Documentation now describes site sets as the supported TYPO3 14 configuration path.
- The local DDEV project has a TYPO3 14 bootstrap services file for CLI/container boot in this standalone extension setup.

## Verification

- `ddev composer validate --strict`: passed.
- `ddev exec .build/vendor/bin/typo3 asset:publish -vvv`: passed.

