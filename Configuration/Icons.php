<?php

declare(strict_types=1);

use In2code\Powermail\Imaging\IconProvider\InlineSvgIconProvider;

return [
    'extension-powermail-main' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/powermail.svg',
    ],
    'module-powermail-list' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/module-powermail-list.svg',
    ],
    'module-powermail-overview' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/module-powermail-overview.svg',
    ],
    'module-powermail-reporting-form' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/module-powermail-reporting-form.svg',
    ],
    'module-powermail-reporting-marketing' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/module-powermail-reporting-marketing.svg',
    ],
    'module-powermail-check' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/module-powermail-check.svg',
    ],
    'plugin-powermail-form' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/plugin-powermail-form.svg',
    ],
    'record-powermail-answer' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/record-powermail-answer.svg',
    ],
    'record-powermail-field' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/record-powermail-field.svg',
    ],
    'record-powermail-form' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/record-powermail-form.svg',
    ],
    'record-powermail-mail' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/record-powermail-mail.svg',
    ],
    'record-powermail-page' => [
        'provider' => InlineSvgIconProvider::class,
        'source' => 'EXT:powermail/Resources/Public/Icons/record-powermail-page.svg',
    ],
];
