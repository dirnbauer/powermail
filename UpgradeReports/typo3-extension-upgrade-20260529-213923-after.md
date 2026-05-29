# TYPO3 extension upgrade report - 2026-05-29 21:39:23

## Result

- Dropped TYPO3 13 support from Composer constraints and CI matrices.
- Updated extension and test dependencies for TYPO3 14 and PHPUnit 11.
- Added `Build/Scripts/runTests.sh` with unit, PHPStan, lint, TypoScript and CI modes.
- Adjusted Fluid ViewHelper signatures required by TYPO3 Fluid 5.
- Replaced direct `ContentObjectRenderer` construction in unit tests with mocks for TYPO3 14 constructor compatibility.

## Verification

- `ddev composer test:unit`: passed, 464 tests, 1591 assertions.
- `ddev exec Build/Scripts/runTests.sh -s ci -p 8.4`: passed.

