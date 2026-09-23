<?php

use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\Mail;
use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;

defined('TYPO3') || die();

/**
 * Offer the powermail mail and answer tables in the scheduler task "Table garbage collection"
 */
if (isset($GLOBALS['TCA']['tx_scheduler_task']['types'][TableGarbageCollectionTask::class])) {
    foreach ([Mail::TABLE_NAME, Answer::TABLE_NAME] as $table) {
        $GLOBALS['TCA']['tx_scheduler_task']['types'][TableGarbageCollectionTask::class]['taskOptions']['tables'][$table] = [
            'dateField' => 'tstamp',
            'expirePeriod' => 30,
        ];
    }
}
