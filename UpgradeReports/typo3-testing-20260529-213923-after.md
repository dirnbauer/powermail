# TYPO3 testing report - 2026-05-29 21:39:23

## Baseline before upgrade

- Composer validation passed.
- PHP lint passed.
- TypoScript lint passed.
- Unit tests passed before the upgrade.
- PHPStan at the old level 8 failed before the upgrade with existing baseline/type issues.

## Result after upgrade

- PHPStan now runs at level max with `saschaegerer/phpstan-typo3` v3.0.1.
- The PHPStan baseline was regenerated for level max and currently contains 2180 existing errors.
- Unit tests run on PHPUnit 11.5.
- A project test runner was added at `Build/Scripts/runTests.sh`.

## Verification

- `ddev composer test:php:lint`: passed.
- `ddev composer test:php:cs`: passed.
- `ddev composer test:typoscript:lint`: passed.
- `ddev composer test:php:phpstan`: passed.
- `ddev composer test:unit`: passed, 464 tests, 1591 assertions.
- `ddev exec Build/Scripts/runTests.sh -s ci -p 8.4`: passed.
