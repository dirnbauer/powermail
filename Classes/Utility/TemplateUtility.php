<?php

declare(strict_types=1);
namespace In2code\Powermail\Utility;

use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Fluid\RestrictedStringRenderer;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;

/**
 * Class TemplateUtility
 */
class TemplateUtility
{
    /**
     *  Get absolute paths for templates with fallback
     *     Returns paths from *RootPaths and "hardcoded"
     *     paths pointing to the EXT:powermail-resources.
     *
     * @codeCoverageIgnore
     */
    public static function getTemplateFolders(string $part = 'template'): array
    {
        $templatePaths = [];
        $extbaseConfig = ObjectUtility::getConfigurationManager()->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'powermail'
        );
        if (!empty($extbaseConfig['view'][$part . 'RootPaths'])) {
            $templatePaths = $extbaseConfig['view'][$part . 'RootPaths'];
            ksort($templatePaths, SORT_NUMERIC);
            $templatePaths = array_values($templatePaths);
        }

        if ($templatePaths === []) {
            $templatePaths[] = 'EXT:powermail/Resources/Private/' . ucfirst($part) . 's/';
        }

        $templatePaths = array_unique($templatePaths);
        $absolutePaths = [];
        foreach ($templatePaths as $templatePath) {
            $absolutePaths[] = StringUtility::addTrailingSlash(GeneralUtility::getFileAbsFileName($templatePath));
        }

        return $absolutePaths;
    }

    /**
     *  Return path and filename for a file or path.
     *  Only the first existing file/path will be returned.
     *  respect *RootPaths
     *
     * @codeCoverageIgnore
     */
    public static function getTemplatePath(string $pathAndFilename, string $part = 'template'): string
    {
        $matches = self::getTemplatePaths($pathAndFilename, $part);
        return $matches === [] ? '' : end($matches);
    }

    /**
     *  Return path and filename for one or many files/paths.
     *         Only existing files/paths will be returned.
     *         respect *RootPaths
     *
     * @codeCoverageIgnore
     */
    public static function getTemplatePaths(string $pathAndFilename, string $part = 'template'): array
    {
        $matches = [];
        $absolutePaths = self::getTemplateFolders($part);
        foreach ($absolutePaths as $absolutePath) {
            if (file_exists($absolutePath . $pathAndFilename)) {
                $matches[] = $absolutePath . $pathAndFilename;
            }
        }

        return $matches;
    }

    /**
     * Get a default Standalone view
     *
     * @codeCoverageIgnore
     */
    public static function getDefaultView(
        string $format = 'html',
        ?ServerRequestInterface $request = null
    ): ViewInterface {
        /** @var ViewFactoryInterface $viewFactory */
        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        return $viewFactory->create(new ViewFactoryData(
            partialRootPaths: self::getTemplateFolders('partial'),
            layoutRootPaths: self::getTemplateFolders('layout'),
            request: $request ?? self::getCurrentRequest(),
            format: $format,
        ));
    }

    protected static function getCurrentRequest(): ?ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        return $request instanceof ServerRequestInterface ? $request : null;
    }

    /**
     * This functions renders the powermail_all Template (e.g. useage in Mails)
     *
     * @codeCoverageIgnore
     */
    public static function powermailAll(
        Mail $mail,
        string $section = 'web',
        array $settings = [],
        ?string $type = null
    ): ?string {
        $view = self::getDefaultView();
        if ($view instanceof FluidViewAdapter) {
            $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename(self::getTemplatePath('Form/PowermailAll.html'));
        }
        $view->assignMultiple(
            [
                'mail' => $mail,
                'section' => $section,
                'settings' => $settings,
                'type' => $type,
            ]
        );
        return $view->render();
    }

    /**
     * Parse String with Fluid View
     *
     * Only variables and the ViewHelpers configured in the extension configuration are evaluated -
     * some of the parsed values can hold data that was submitted by a website visitor, which would
     * otherwise allow arbitrary ViewHelper execution from an unauthenticated request.
     *
     * @param array<string, mixed> $variables
     */
    public static function fluidParseString(string $string, array $variables = []): string
    {
        if ($string === '' || $string === '0'
            || ConfigurationUtility::isDatabaseConnectionAvailable() === false
            || BackendUtility::isBackendContext()
            || Environment::isCli()
        ) {
            return $string;
        }

        return GeneralUtility::makeInstance(RestrictedStringRenderer::class)->render($string, $variables);
    }
}
