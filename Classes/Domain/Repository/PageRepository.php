<?php

declare(strict_types=1);

namespace In2code\Powermail\Domain\Repository;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Powermail\Domain\Model\Form;
use In2code\Powermail\Domain\Model\Page;
use In2code\Powermail\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PageRepository
 */
class PageRepository extends AbstractRepository
{
    public function getPageNameFromUid(int $uid): string
    {
        $pageName = '';
        $query = $this->createQuery();
        $sql = 'select uid,title from pages where uid = ' . $uid . ' limit 1';
        $result = $query->statement($sql)->execute(true);
        if (!empty($result[0]['title'])) {
            return $result[0]['title'];
        }

        return $pageName;
    }

    /**
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getPropertiesFromUid(int $uid): array
    {
        $connection = DatabaseUtility::getConnectionForTable('pages');
        $properties = $connection->executeQuery('select * from pages where uid=' . $uid . ' limit 1')->fetchAssociative();
        return $properties ?: [];
    }

    /**
     * Get all pages with tt_content with a Powermail Plugin
     *
     * Which form a plugin shows is read from its parsed FlexForm, as the page module preview reads
     * it. Matching the XML as text found a plugin only if its value stood on a line of its own,
     * indented by exactly twenty spaces - the layout the backend writes today. A plugin written any
     * other way was missed although its FlexForm is the same: by a seeder or an import that puts
     * the value on the field's line, or everything on one line.
     *
     * @return list<array{uid: int, title: string}>
     * @throws ExceptionDbal
     */
    public function getPagesWithContentRelatedToForm(Form $form): array
    {
        $formUid = (int)$form->getUid();
        if ($formUid <= 0) {
            return [];
        }

        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('tt_content', true);
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $rows = $queryBuilder
            ->select('pages.uid', 'pages.title', 'tt_content.pi_flexform')
            ->from('tt_content')
            ->join(
                'tt_content',
                'pages',
                'pages',
                $queryBuilder->expr()->eq('pages.uid', $queryBuilder->quoteIdentifier('tt_content.pid'))
            )
            ->where(
                $queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter('powermail_pi1')),
                // Narrows the candidates down without assuming a layout; the parsed FlexForm decides.
                $queryBuilder->expr()->like(
                    'tt_content.pi_flexform',
                    $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards((string)$formUid) . '%')
                )
            )
            ->orderBy('pages.uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $pages = [];
        foreach ($rows as $row) {
            $pageUid = $row['uid'] ?? null;
            $flexForm = $row['pi_flexform'] ?? null;
            if (!is_numeric($pageUid) || !is_string($flexForm) || isset($pages[(int)$pageUid])) {
                continue;
            }
            if (in_array($formUid, $this->getFormUidsFromFlexForm($flexForm), true)) {
                $title = $row['title'] ?? null;
                $pages[(int)$pageUid] = ['uid' => (int)$pageUid, 'title' => is_string($title) ? $title : ''];
            }
        }

        return array_values($pages);
    }

    /**
     * The forms a plugin FlexForm selects in settings.flexform.main.form
     *
     * @return list<int>
     */
    protected function getFormUidsFromFlexForm(string $flexForm): array
    {
        if (trim($flexForm) === '') {
            return [];
        }
        $settings = GeneralUtility::makeInstance(FlexFormTools::class)->convertFlexFormContentToArray($flexForm);
        $path = 'settings/flexform/main/form';
        if (!ArrayUtility::isValidPath($settings, $path)) {
            return [];
        }
        $forms = ArrayUtility::getValueByPath($settings, $path);
        if (!is_string($forms) && !is_int($forms)) {
            return [];
        }

        return GeneralUtility::intExplode(',', (string)$forms, true);
    }

    /**
     * Find all localized records with
     *        tx_powermail_domain_model_page.form = "0"
     *
     * @throws ExceptionDbal
     */
    public function findAllWrongLocalizedPages(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Page::TABLE_NAME, true);
        return $queryBuilder
            ->select('uid', 'pid', 'title', 'l10n_parent', 'sys_language_uid')
            ->from(Page::TABLE_NAME)
            ->where("(form = '' or form = 0) and sys_language_uid > 0 and deleted = 0")
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Fix wrong localized forms
     */
    public function fixWrongLocalizedPages(): void
    {
        foreach ($this->findAllWrongLocalizedPages() as $page) {
            $defaultPageUid = $page['l10n_parent'];
            $defaultFormUid = $this->getFormUidFromPageUid($defaultPageUid);
            $localizedFormUid = $this->getLocalizedFormUidFromFormUid($defaultFormUid, $page['sys_language_uid']);
            $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Page::TABLE_NAME);
            $queryBuilder
                ->update(Page::TABLE_NAME)
                ->where('uid = ' . (int)$page['uid'])
                ->set('form', $localizedFormUid)
                ->executeStatement();
        }
    }

    /**
     * Get all not deleted pages
     *
     * @return int[]
     * @throws ExceptionDbal
     */
    public function getAllPages(): array
    {
        $querybuilder = DatabaseUtility::getQueryBuilderForTable('pages', true);
        $rows = $querybuilder->select('uid')->from('pages')->executeQuery()->fetchAllAssociative();
        $pids = [];
        foreach ($rows as $row) {
            $pids[] = (int)$row['uid'];
        }

        return $pids;
    }

    /**
     * Get parent form uid form given page uid
     */
    protected function getFormUidFromPageUid(int $pageUid): int
    {
        $query = $this->createQuery();
        $sql = 'select form';
        $sql .= ' from ' . Page::TABLE_NAME;
        $sql .= ' where uid = ' . $pageUid;
        $sql .= ' and deleted = 0';
        $sql .= ' limit 1';
        $row = $query->statement($sql)->execute(true);
        return (int)$row[0]['form'];
    }

    protected function getLocalizedFormUidFromFormUid(int $formUid, int $sysLanguageUid): int
    {
        $query = $this->createQuery();
        $sql = 'select uid';
        $sql .= ' from ' . Form::TABLE_NAME;
        $sql .= ' where l10n_parent = ' . $formUid;
        $sql .= ' and sys_language_uid = ' . $sysLanguageUid;
        $sql .= ' and deleted = 0';
        $row = $query->statement($sql)->execute(true);
        return (int)$row[0]['uid'];
    }
}
