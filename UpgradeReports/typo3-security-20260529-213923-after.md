# TYPO3 security report - 2026-05-29 21:39:23

## Result

- Local project password hashing configuration uses Argon2id.
- DDEV trusted host patterns are restricted to `powermail-v14.ddev.site`, `localhost` and `127.0.0.1`.
- TYPO3 13 support was removed, reducing supported runtime surface.
- PHP runtime support is limited to PHP 8.3 through 8.5.

## Verification

- `ddev composer validate --strict`: passed.
- `ddev composer test:php:lint`: passed.

