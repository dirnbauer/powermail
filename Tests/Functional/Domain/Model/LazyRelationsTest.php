<?php

declare(strict_types=1);

namespace In2code\Powermail\Tests\Functional\Domain\Model;

use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Model\Form;
use In2code\Powermail\Domain\Model\Page;
use In2code\Powermail\Domain\Repository\FormRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Form::$pages and Page::$fields are lazy (as upstream intends): loading a form must not load its pages and
 * fields, and every way powermail reads them - iteration, count(), the *By* helpers - must see the real records
 * in their sorting order.
 */
#[CoversClass(Form::class)]
#[CoversClass(Page::class)]
final class LazyRelationsTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'scheduler',
    ];

    protected array $testExtensionsToLoad = [
        'in2code/powermail',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/lazy_relations.csv');
    }

    #[Test]
    public function loadingAFormLeavesPagesAndFieldsUnloaded(): void
    {
        $form = $this->findForm();
        $pages = $form->_getProperty('pages');

        self::assertInstanceOf(LazyObjectStorage::class, $pages);
        self::assertFalse($pages->isInitialized());
        // count() answers with a COUNT query and keeps the storage unloaded.
        self::assertCount(2, $form->getPages());
        self::assertFalse($pages->isInitialized());
    }

    #[Test]
    public function iteratingLoadsPagesAndFieldsInSortingOrder(): void
    {
        $form = $this->findForm();

        $pageTitles = [];
        $markers = [];
        foreach ($form->getPages() as $page) {
            self::assertInstanceOf(Page::class, $page);
            $fields = $page->_getProperty('fields');
            self::assertInstanceOf(LazyObjectStorage::class, $fields);
            self::assertFalse($fields->isInitialized());
            $pageTitles[] = $page->getTitle();
            foreach ($page->getFields() as $field) {
                self::assertInstanceOf(Field::class, $field);
                $markers[] = $field->getMarker();
            }
            self::assertTrue($fields->isInitialized());
        }

        self::assertSame(['Page one', 'Page two'], $pageTitles);
        self::assertSame(['firstname', 'email', 'message'], $markers);
    }

    #[Test]
    public function formAndPageHelpersResolveTheLazyRelations(): void
    {
        $form = $this->findForm();

        self::assertSame(
            ['firstname', 'email', 'message'],
            array_map(static fn (Field $field): string => $field->getMarker(), $form->getFields())
        );
        self::assertSame([12, 11], array_keys($form->getPagesByUid()));
        self::assertSame(['page_one', 'page_two'], array_keys($form->getPagesByTitle()));
        self::assertSame(['firstname', 'email'], array_keys($form->getPagesByUid()[12]->getFieldsByFieldMarker()));
        self::assertSame([103], array_keys($form->getPagesByUid()[11]->getFieldsByFieldUid()));
    }

    private function findForm(): Form
    {
        $form = $this->get(FormRepository::class)->findByUid(1);
        self::assertInstanceOf(Form::class, $form);
        return $form;
    }
}
