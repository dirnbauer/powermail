<?php

declare(strict_types=1);

namespace In2code\Powermail\Imaging\IconProvider;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

class InlineSvgIconProvider extends SvgIconProvider
{
    protected function generateMarkup(Icon $icon, array $options): string
    {
        return $this->generateInlineMarkup($options);
    }
}
