<?php

declare(strict_types=1);
namespace In2code\Powermail\ViewHelpers\Misc;

use In2code\Powermail\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class CheckForTypoScriptViewHelper
 * @noinspection PhpUnused
 */
class CheckForTypoScriptViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('settings', 'array', 'settings array', true);
    }

    public function render(): string
    {
        $this->addMissingTypoScriptFlashMessage($this->arguments['settings'] ?? []);
        return '';
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): void {
        self::addMissingTypoScriptFlashMessage($arguments['settings'] ?? []);
    }

    protected static function addMissingTypoScriptFlashMessage(array $settings): void
    {
        if (($settings['staticTemplate'] ?? 1) === '1') {
            return;
        }

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        /** @var FlashMessageQueue $flashMessageQueue */
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('extbase.flashmessages.tx_powermail_pi1');
        /** @var FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            LocalizationUtility::translate('error_no_typoscript'),
            '',
            \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR
        );
        $flashMessageQueue->addMessage($flashMessage);
    }
}
