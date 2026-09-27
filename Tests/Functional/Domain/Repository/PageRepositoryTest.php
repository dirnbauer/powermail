<?php

declare(strict_types=1);

namespace In2code\Powermail\Tests\Functional\Domain\Repository;

use In2code\Powermail\Domain\Model\Form;
use In2code\Powermail\Domain\Repository\PageRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The form overview's "Used on Page" column. A plugin's FlexForm is the same whether the backend
 * wrote it or a seeder did, whatever its layout: the value on a line of its own, on the field's
 * line, or everything on one line. The column was empty for every plugin not in the backend's layout.
 */
#[CoversClass(PageRepository::class)]
final class PageRepositoryTest extends FunctionalTestCase
{
    private const FORM = 42;

    protected array $coreExtensionsToLoad = [
        'scheduler',
    ];

    protected array $testExtensionsToLoad = [
        'in2code/powermail',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/form_usage.csv');
    }

    #[Test]
    public function aFormIsUsedOnEveryPageWhosePluginSelectsItWhateverTheXmlLayout(): void
    {
        $this->insertPlugin(2, $this->backendLayout((string)self::FORM));
        $this->insertPlugin(3, $this->seederLayout((string)self::FORM));
        $this->insertPlugin(4, str_replace(["\n", '    '], '', $this->seederLayout((string)self::FORM)));
        $this->insertPlugin(5, $this->backendLayout((string)self::FORM));
        $this->insertPlugin(5, $this->seederLayout((string)self::FORM));
        $this->insertPlugin(6, $this->backendLayout('9,' . self::FORM));

        self::assertSame(
            [
                ['uid' => 2, 'title' => 'Saved in the backend'],
                ['uid' => 3, 'title' => 'Written by a seeder'],
                ['uid' => 4, 'title' => 'Written on one line'],
                ['uid' => 5, 'title' => 'Two plugins'],
                ['uid' => 6, 'title' => 'Two forms in one plugin'],
            ],
            $this->get(PageRepository::class)->getPagesWithContentRelatedToForm($this->form(self::FORM))
        );
    }

    #[Test]
    public function aPluginThatOnlyMentionsTheUidDoesNotUseTheForm(): void
    {
        $this->insertPlugin(7, $this->backendLayout('4' . self::FORM));
        $this->insertPlugin(8, $this->backendLayout('8', (string)self::FORM));
        $this->insertPlugin(9, $this->backendLayout((string)self::FORM), true);
        $this->insertPlugin(10, $this->backendLayout((string)self::FORM));

        self::assertSame(
            [],
            $this->get(PageRepository::class)->getPagesWithContentRelatedToForm($this->form(self::FORM))
        );
    }

    /**
     * The FlexForm as DataHandler writes it when an editor saves the plugin
     */
    private function backendLayout(string $form, string $storagePid = '0'): string
    {
        return $this->get(FlexFormTools::class)->flexArray2Xml([
            'data' => [
                'main' => [
                    'lDEF' => [
                        'settings.flexform.main.form' => ['vDEF' => $form],
                        'settings.flexform.main.pid' => ['vDEF' => $storagePid],
                    ],
                ],
            ],
        ]);
    }

    /**
     * The FlexForm as the Desiderio and Jev demo seeders wrote it: each value on its field's line
     */
    private function seederLayout(string $form): string
    {
        return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>' . "\n"
            . "<T3FlexForms>\n    <data>\n        <sheet index=\"main\">\n            <language index=\"lDEF\">\n"
            . '                <field index="settings.flexform.main.form"><value index="vDEF">' . $form . "</value></field>\n"
            . '                <field index="settings.flexform.main.pid"><value index="vDEF">0</value></field>' . "\n"
            . "            </language>\n        </sheet>\n    </data>\n</T3FlexForms>";
    }

    private function insertPlugin(int $pid, string $flexForm, bool $deleted = false): void
    {
        $this->getConnectionPool()->getConnectionForTable('tt_content')->insert('tt_content', [
            'pid' => $pid,
            'CType' => 'powermail_pi1',
            'pi_flexform' => $flexForm,
            'deleted' => (int)$deleted,
        ]);
    }

    private function form(int $uid): Form
    {
        $form = new Form();
        $form->_setProperty('uid', $uid);
        return $form;
    }
}
