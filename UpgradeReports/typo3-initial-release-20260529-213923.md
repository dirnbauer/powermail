# TYPO3 initial release readiness report - 2026-05-29 21:39:23

## Release target

- Extension: `in2code/powermail`
- Version: `14.0.0`
- TYPO3: `^14.3`
- PHP: `>=8.3 <8.6`
- Branch: `typo3-v14`

## Status

- Dependency constraints, local development environment, PHPStan config, tests and docs were updated for the TYPO3 14 line.
- The release still carries a PHPStan baseline for pre-existing strictness debt.
- PHPUnit reports 152 deprecations from doc-comment metadata; tests pass on PHPUnit 11.

## Verification

- `ddev composer validate --strict`: passed.
- `ddev composer install`: passed.
- `ddev composer test:php:lint`: passed.
- `ddev composer test:php:cs`: passed.
- `ddev composer test:typoscript:lint`: passed.
- `ddev composer test:php:phpstan`: passed.
- `ddev composer test:unit`: passed.
- `ddev exec Build/Scripts/runTests.sh -s ci -p 8.4`: passed.
- `ddev describe`: web, db, chromedriver and solr are running.
