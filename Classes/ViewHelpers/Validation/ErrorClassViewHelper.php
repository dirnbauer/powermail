<?php

declare(strict_types=1);

namespace In2code\Powermail\ViewHelpers\Validation;

use Doctrine\DBAL\DBALException;
use In2code\Powermail\Domain\Model\Field;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns Error Class if Error in form
 */
class ErrorClassViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('field', Field::class, 'Field', true);
        $this->registerArgument('class', 'string', 'Class name', false, 'error');
    }

    /**
     * @throws DBALException
     */
    public function render(): string
    {
        /** @var Field $field */
        $field = $this->arguments['field'];
        $request = $this->getRequest();
        $extbaseRequestParameters = $request?->getAttribute('extbase');
        if (!$extbaseRequestParameters instanceof ExtbaseRequestParameters) {
            return '';
        }

        $validationResults = $extbaseRequestParameters->getOriginalRequestMappingResults();
        $errors = $validationResults->getFlattenedErrors();
        foreach ($errors as $error) {
            /** @var Error $singleError */
            foreach ((array)$error as $singleError) {
                if (!empty($singleError->getArguments()['marker'])
                    && $field->getMarker() === $singleError->getArguments()['marker']) {
                    return $this->arguments['class'];
                }
            }
        }

        return '';
    }

    /**
     * Shortcut for retrieving the request from the controller context
     *
     * @return ServerRequestInterface|null
     */
    protected function getRequest(): ?ServerRequestInterface
    {
        $renderingContext = $this->renderingContext;
        if ($renderingContext !== null && $renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return $renderingContext->getAttribute(ServerRequestInterface::class);
        }
        return null;
    }
}
