# TYPO3 update report before changes - 2026-05-29 21:11:22

## Scope

- Target TYPO3 version: 14.3+ only.
- Target PHP support: 8.3 through 8.5, with DDEV running PHP 8.4.
- Current branch before work: `typo3-v13`.
- Upgrade branch created for this work: `typo3-v14`.

## Initial findings

- `composer.json` currently requires `typo3/cms-core:^13.4`.
- `ext_emconf.php` currently declares TYPO3 `13.4.0-13.4.99`.
- DDEV exists and currently uses PHP 8.2 with project name `powermail-v13`.
- `.project/tests/phpstan.neon` currently uses PHPStan level 8.
- Sitesets already exist in `Configuration/Sets/*`.
- DDEV was installed but stopped when the upgrade began.

## Backup

- File backup created at
  `../powermail-upgrade-backups/powermail-files-pre-typo3-v14-20260529-211122.tar.gz`.
- Database backup was not exported at this moment because the DDEV database
  service was stopped.
