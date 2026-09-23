<?php

declare(strict_types=1);

namespace In2code\Powermail\Tests\Functional\Configuration;

use In2code\Powermail\Controller\FormController;
use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\BackendUser;
use In2code\Powermail\Domain\Model\BackendUserGroup;
use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Model\Form;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Model\Page;
use In2code\Powermail\Domain\Validator\CaptchaValidator;
use In2code\Powermail\Domain\Validator\CustomValidator;
use In2code\Powermail\Domain\Validator\ForeignValidator;
use In2code\Powermail\Domain\Validator\InputValidator;
use In2code\Powermail\Domain\Validator\PasswordValidator;
use In2code\Powermail\Domain\Validator\SpamShieldValidator;
use In2code\Powermail\Domain\Validator\UniqueValidator;
use In2code\Powermail\Domain\Validator\UploadValidator;
use In2code\Powermail\Utility\BasicFileUtility;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\Tca\TcaFactory;
use TYPO3\CMS\Core\Configuration\Tca\TcaMigration;
use TYPO3\CMS\Core\Crypto\HashAlgo;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Schema\SearchableSchemaFieldsCollector;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Reflection\ClassSchema;
use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Guards the TYPO3 v14 registration of powermail: nothing may rely on deprecated core APIs (ext_tables.php, TCA
 * "searchFields", addPiFlexFormValue(), array-configured or method-level Extbase validation attributes, EXT:install
 * upgrade wizard interfaces, the $GLOBALS garbage collection configuration), and the migrated configuration must
 * behave exactly like the configuration it replaces.
 */
#[CoversNothing]
final class Typo3V14RegistrationTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'scheduler',
    ];

    protected array $testExtensionsToLoad = [
        'in2code/powermail',
    ];

    #[Test]
    public function extensionShipsNoExtTablesFile(): void
    {
        self::assertFileDoesNotExist(ExtensionManagementUtility::extPath('powermail') . 'ext_tables.php');
    }

    #[Test]
    public function powermailTcaNeedsNoCoreMigration(): void
    {
        $messages = (new TcaMigration())->migrate($this->get(TcaFactory::class)->createNotMigrated())->getMessages();

        self::assertSame(
            [],
            array_values(array_filter($messages, static fn (string $message): bool => str_contains($message, 'powermail'))),
        );
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function searchableFieldsDataProvider(): array
    {
        // These are the lists the removed ctrl "searchFields" option used to hold.
        return [
            'forms' => [Form::TABLE_NAME, ['title']],
            'pages' => [Page::TABLE_NAME, ['title']],
            'fields' => [Field::TABLE_NAME, ['title']],
            'mails' => [Mail::TABLE_NAME, ['body', 'sender_mail', 'sender_name', 'subject']],
        ];
    }

    /**
     * @param list<string> $expectedFields
     */
    #[Test]
    #[DataProvider('searchableFieldsDataProvider')]
    public function backendSearchUsesTheFieldsOfTheFormerSearchFieldsList(string $table, array $expectedFields): void
    {
        $fields = $this->get(SearchableSchemaFieldsCollector::class)->getFieldNames($table);
        sort($fields);

        self::assertSame($expectedFields, $fields);
    }

    #[Test]
    public function pluginFlexFormIsRegisteredForTheContentType(): void
    {
        $type = $GLOBALS['TCA']['tt_content']['types']['powermail_pi1'];

        self::assertSame(
            'FILE:EXT:powermail/Configuration/FlexForms/FlexformPi1.xml',
            $type['columnsOverrides']['pi_flexform']['config']['ds'] ?? null
        );
        self::assertStringContainsString('pi_flexform', $type['showitem']);
    }

    #[Test]
    public function tableGarbageCollectionOffersMailsAndAnswers(): void
    {
        $tables = (new TableGarbageCollectionTask())->getTableConfiguration();

        foreach ([Mail::TABLE_NAME, Answer::TABLE_NAME] as $table) {
            self::assertSame(['dateField' => 'tstamp', 'expirePeriod' => 30], $tables[$table] ?? null);
        }
        self::assertArrayNotHasKey(
            TableGarbageCollectionTask::class,
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'] ?? []
        );
    }

    #[Test]
    public function liveSearchCommandsAreRegistered(): void
    {
        self::assertSame(Mail::TABLE_NAME, $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['mail'] ?? null);
        self::assertSame(Form::TABLE_NAME, $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['form'] ?? null);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function validatedActionsDataProvider(): array
    {
        return [
            'confirmation' => ['confirmationAction'],
            'create' => ['createAction'],
        ];
    }

    #[Test]
    #[DataProvider('validatedActionsDataProvider')]
    public function mailArgumentKeepsItsValidatorsInOrder(string $action): void
    {
        $deprecations = $this->collectDeprecations(
            static fn (): ClassSchema => new ClassSchema(FormController::class),
            $classSchema
        );

        self::assertSame([], $deprecations);
        self::assertSame(
            [
                UploadValidator::class,
                InputValidator::class,
                PasswordValidator::class,
                CaptchaValidator::class,
                SpamShieldValidator::class,
                UniqueValidator::class,
                ForeignValidator::class,
                CustomValidator::class,
            ],
            array_column($classSchema->getMethod($action)->getParameter('mail')->getValidators(), 'className')
        );
    }

    #[Test]
    public function modelAttributesUseTheExtbaseAttributeNamespace(): void
    {
        $deprecations = $this->collectDeprecations(
            static fn (): array => [
                new ClassSchema(BackendUser::class),
                new ClassSchema(BackendUserGroup::class),
                new ClassSchema(Mail::class),
                new ClassSchema(Form::class),
                new ClassSchema(Page::class),
            ],
            $schemas
        );

        self::assertSame([], $deprecations);
        [$backendUser, $backendUserGroup, $mail, $form, $page] = $schemas;
        self::assertSame('NotEmpty', $backendUser->getProperty('userName')->getValidators()[0]['name'] ?? null);
        self::assertSame('NotEmpty', $backendUserGroup->getProperty('title')->getValidators()[0]['name'] ?? null);
        self::assertTrue($mail->getProperty('feuser')->isLazy());
        self::assertTrue($mail->getProperty('answers')->isLazy());
        // TYPO3 14 reads only attributes: the doc-comment @Lazy annotations these two carried were ignored.
        self::assertTrue($form->getProperty('pages')->isLazy());
        self::assertTrue($page->getProperty('fields')->isLazy());
    }

    #[Test]
    public function upgradeWizardsAreRegisteredThroughTheCoreAttribute(): void
    {
        $registry = $this->get(UpgradeWizardRegistry::class);

        foreach ([
            'powermailLanguageUpdateWizard',
            'powermailPermissionSubmodulesUpdater',
            'powermailPermissionUpdater',
            'powermailPluginUpdater',
            'powermailRelationUpdateWizard',
            'powermailTextNullUpdateWizard',
        ] as $identifier) {
            self::assertTrue($registry->hasUpgradeWizard($identifier), $identifier);
        }
    }

    #[Test]
    public function fileHmacUsesTheCoreHashService(): void
    {
        $hmac = BasicFileUtility::getHmacForFile('/uploads/tx_powermail/file.pdf');

        self::assertSame(
            $this->get(HashService::class)->hmac('/uploads/tx_powermail/file.pdf', '_powermail', HashAlgo::SHA3_256),
            $hmac
        );
        self::assertNotSame($hmac, BasicFileUtility::getHmacForFile('/uploads/tx_powermail/other.pdf'));
    }

    /**
     * Run $callback and return every E_USER_DEPRECATED message it raised.
     *
     * @param callable(): mixed $callback
     * @param-out mixed $result
     * @return list<string>
     */
    private function collectDeprecations(callable $callback, mixed &$result = null): array
    {
        $deprecations = [];
        set_error_handler(
            static function (int $level, string $message) use (&$deprecations): bool {
                $deprecations[] = $message;
                return true;
            },
            E_USER_DEPRECATED
        );
        try {
            $result = $callback();
        } finally {
            restore_error_handler();
        }
        return $deprecations;
    }
}
