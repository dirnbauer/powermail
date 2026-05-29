#!/usr/bin/env bash
set -euo pipefail

SUITE="unit"
PHP_VERSION=""

while getopts "s:p:h" OPTION; do
    case "${OPTION}" in
        s) SUITE="${OPTARG}" ;;
        p) PHP_VERSION="${OPTARG}" ;;
        h)
            echo "Usage: $0 -s unit|phpstan|cgl|lint|typoscript|ci [-p 8.3|8.4|8.5]"
            exit 0
            ;;
        *) exit 1 ;;
    esac
done

if [[ -n "${PHP_VERSION}" ]]; then
    CURRENT_PHP="$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')"
    if [[ "${CURRENT_PHP}" != "${PHP_VERSION}" ]]; then
        echo "Requested PHP ${PHP_VERSION}, running PHP ${CURRENT_PHP}."
        echo "Switch PHP in DDEV or CI before running this suite."
        exit 1
    fi
fi

case "${SUITE}" in
    unit)
        composer test:unit
        ;;
    phpstan)
        composer test:php:phpstan
        ;;
    cgl)
        composer test:php:cs
        ;;
    lint)
        composer test:php:lint
        ;;
    typoscript)
        composer test:typoscript:lint
        ;;
    ci)
        composer test:php:lint
        composer test:typoscript:lint
        composer test:php:phpstan
        composer test:unit
        ;;
    *)
        echo "Unknown suite: ${SUITE}"
        exit 1
        ;;
esac
