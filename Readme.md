# TYPO3 Extension powermail

Powermail is a well-known, editor-friendly, powerful and easy to use mailform extension for TYPO3 with a lots of
features (spam prevention, marketing information, optin, ajax submit, diagram analysis, etc...).

## Fork notes

This is webconsulting's fork of [in2code-de/powermail](https://github.com/in2code-de/powermail). in2code has not
published a TYPO3 14 release, so this fork ports the upstream `typo3-v13` line to TYPO3 14.3 LTS. The Composer
package name stays `in2code/powermail`.

What differs from upstream:

- **Branch `typo3-v14`** is upstream `typo3-v13` (currently 13.3.0 plus its later bugfixes) merged into the TYPO3 14
  port: Extbase controller attributes, `ViewFactory` rendering, Fluid 5 ViewHelper signatures, the v14 SVG icon family,
  plugin lookups for the v14 content types, answer-value validation, request guards for CLI and MCP writes, and the
  13.2.1 security fixes adapted to v14. The fork's own changes are listed under "Fork changes" in
  [the changelog](/Documentation/Changelog/Index.md).
- **No TYPO3 14.3 deprecations** (since 14.0.3.2): no `ext_tables.php`, per-column `searchable` instead of the
  removed TCA `searchFields`, the plugin FlexForm passed to `registerPlugin()` instead of `addPiFlexFormValue()`,
  Extbase validation attributes on the action parameter, upgrade wizards on the EXT:core interfaces, and the table
  garbage collection configured in TCA. `Tests/Functional/Configuration/Typo3V14RegistrationTest.php` guards this.
- **Lazy form pages and page fields** (since 14.0.3.3): `Form::$pages` and `Page::$fields` use the v14
  `#[Extbase\Attribute\ORM\Lazy]` attribute. Upstream still uses the doc-comment annotation, which works on TYPO3 13
  but is ignored by TYPO3 14, where both relations would load eagerly.
- TYPO3 14.3.6 or newer and PHP 8.3 to 8.5 only; Composer installation only (no `ext_emconf.php`).
- PHPStan runs at level max with a baseline (upstream: level 8 with a baseline).

Installing the fork:

```json
{
    "repositories": [{"type": "vcs", "url": "https://github.com/dirnbauer/powermail.git"}],
    "require": {"in2code/powermail": "~14.0.3.3"}
}
```

Release tags have four parts, `<line>.<revision>`: `14.0.3.3` is the third fork revision of the 14.0.3 line. Require
them with `~` and all four parts. `~14.0.3.3` accepts later fork revisions such as `14.0.3.4`, but never a three-part
upstream tag. `^14.0` would accept any upstream 14.x tag that ever reached this repository, even one without the
fork's changes. A build suffix does not help either: Composer drops `+webcon.1`, so a `14.0.4+webcon.1` tag would
be treated as the same version as upstream's `14.0.4`.

Syncing upstream: the `upstream` remote is fetched with `--no-tags`, so upstream tags never reach this repository.
New upstream commits are merged, never rebased, because Composer consumers pin commits of this branch.

## 1. Documentation overview

* [Introduction](/Documentation/Index.md)
* [Development Model](/Documentation/DevelopmentModel.md)
* [Documentation for editors](/Documentation/ForEditors/Index.md)
* [Documentation for administrators](/Documentation/ForAdministrators/Index.md)
* [Documentation for developers](/Documentation/ForDevelopers/Index.md)
* [FAQ](/Documentation/FAQ/Index.md) (with a lot of stuff and best practice)
* [Support](/Documentation/Support/Index.md)
* [Additional links](/Documentation/Links/Index.md)

## 2. Installation

Quick guide:
- Just install this extension - e.g. `composer require in2code/powermail`
- Add the powermail site set to your TYPO3 site configuration
- Add a new form (with one or more pages and with some fields to a page or a folder)
- Add a new pagecontent (plugin) with type "powermail" and choose the former saved form
- That's all, you can view the result in the frontend

## 3. Administration corner

### 3.1 Versions and Support

| Powermail   | TYPO3     | PHP       | Support/Development                           |
|-------------|-----------|-----------|-----------------------------------------------|
| 14.x        | 14.3+     | 8.3 - 8.5 | Features, Bugfixes, Security Updates          |
| 13.x        | 13.x      | 8.3 - 8.x | Support dropped                               |
| 12.x        | 12.x      | 8.1 - 8.2 | Bugfixes, Security Updates                    |
| 11.x        | 12.x      | 8.1 - 8.2 | Support dropped                               |
| 10.x        | 11.x      | 7.4 - 8.1 | Support dropped (paid backports are possible) |
| 9.x         | 11.x      | 7.4       | Support dropped                               |
| 8.x         | 10.x      | 7.2 - 7.4 | Support dropped (paid backports are possible) |
| 7.x         | 8.7 - 9.x | 7.0 - 7.4 | Support dropped                               |
| 6.x         | 8.7 - 9.x | 7.0 - 7.x | Support dropped                               |

Do you need free support? There is a kind TYPO3 community that could help you.
You can ask questions at https://stackoverflow.com and tag your question with `TYPO3` and `Powermail`.
In addition there is a slack channel in the TYPO3 slack `ext-powermail`.

### 3.2 Suggested Extensions for powermail

- **email2powermail** Automatically convert emails to a link to a powermail form [Link](https://github.com/einpraegsam/email2powermail)
- **powermailrecaptcha** Google recaptcha [Link](https://github.com/einpraegsam/powermailrecaptcha)
- **invisiblerecaptcha** Google invisible recaptcha [Link](https://github.com/einpraegsam/invisiblerecaptcha)
- **powermailextended** Is just an example extension how to extend powermail with new fields or use signals [Link](https://github.com/einpraegsam/powermailextended)
- **powermail_cond** Add conditions (via AJAX) to powermail forms for fields and pages [Link](https://github.com/einpraegsam/powermail_cond)
- **powermail_fastexport** Extend powermail for faster export to .xlsx / .csv files. This is useful if you have many records to be exported. [Link](https://github.com/bithost-gmbh/powermail_fastexport)

### 3.3 Product Owner

The product owner and author of the extension is Marcus Schwemer from [in2code](https://www.in2code.de). Beside that every
in2code colleague is allowed to support further development if she/he wants. In addition there are a lot of other
contributors that helped to improve the extension with their *Pull Requests* - thank you for that!

### 3.4 Release Management

Powermail uses **semantic versioning** which basically means for you, that
- **bugfix updates** (e.g. 1.0.0 => 1.0.1) just includes small bugfixes or security relevant stuff without breaking changes.
- **minor updates** (e.g. 1.0.0 => 1.1.0) includes new features and smaller tasks without breaking changes.
- **major updates** (e.g. 1.0.0 => 2.0.0) normally includes basic refactoring, new features and also breaking changes.

### 3.5 Automatic Testing

#### Behaviour tests

There is a huge testparcours that have to be passed before every release. For example there is an
[automatic test](/Tests/Behavior/Features/Pi1/Validation/Input/JsPhpValidation.feature)
where the browser tries to submit 18 different strings and numbers to a field that accepts only phone numbers to test
serverside validation. After that the same process is done for clientside valiation.
There are also some smaller tests like "Is it possible to submit a form on a page where two different forms are stored?".

See [readme.md](/Tests/Behavior/readme.md) for some more information about behat and selenium tests on powermail.

#### Unit tests

At the moment powermail offers many unit tests that have to be passed before every release. See more information
about unit tests or code coverage in powermail in the [readme.md](/Tests/Unit/readme.md)

### 3.6 Code quality

Beside respecting PSR-12 and TYPO3 coding guidelines, it's very important for the project to leave a file cleaner as before.
Especially because it's a really large extension with a lot of functionality and a history of over 17 years (!) and of course some
technical debts, that have to be fixed step by step.

Current quality tools are:

- php-cs-fixer
- phpstan (level max)
- php linter
- TypoScript linter

### 3.7 Contribution

**Pull requests** are welcome in general! Nevertheless please don't forget to add a description to your pull requests. This
is very helpful to understand what kind of issue the **PR** is going to solve.

- Bugfixes: Please describe what kind of bug your fix solve and give us feedback how to reproduce the issue. We're going
to accept only bugfixes if I can reproduce the issue.
- Features: Not every feature is relevant for the bulk of powermail users. In addition: We don't want to make powermail
even more complicated in usability for an edge case feature. Please discuss a new feature before.


### 3.8 Development

Compile and minify (uglify) JavaScript, compress CSS:

```
$ cd Resources/Private
$ npm install
$ ./node_modules/.bin/gulp
```


## 4. Screenshots

### 4.1 Example form with bootstrap classes:

![Example form](Documentation/Images/frontend1.png "Example Form")


### 4.2 Backend module mail list:

![Backend Module](Documentation/Images/backend1.png "Backend Module")


### 4.3 Backend module reporting:

![Backend Module2](Documentation/Images/backend2.png "Backend Module2")

## 5. Additional supplementary extensions

### 5.1 EXT:powermail_frontend

This extension contains the previous plugins pi2 - pi4. Since the public release of v13 this functionality is only
available as a premium extension.

### 5.2 EXT:powermail_cleaner

EXT:powermail_cleaner gives administrators and editors more flexibility in cleaning up saved email records and in
displaying additional data protection texts per form. This is available as a premium extension.

### 5.4 EXT:powermail_powerpack

This extension will collect some additional functionality and will be available as a premium extension.
