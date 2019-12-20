#!/usr/bin/env bash

# See
#
# - https://docs.travis-ci.com/user/languages/php#Disabling-preinstalled-PHP-extensions
# - https://docs.travis-ci.com/user/languages/php#Custom-PHP-configuration

set -Eeuo pipefail

readonly XDEBUG_ENABLED_INI_FILE="/home/travis/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini"
readonly XDEBUG_DISABLED_INI_FILE="/tmp/xdebug.ini"

#######################################
# Disables the Xdebug extension
#
# Globals:
#   XDEBUG_ENABLED_INI_FILE
#   XDEBUG_DISABLED_INI_FILE
#
# Arguments:
#   None
#
# Returns:
#   None
#######################################
function xdebug_disable() {
    if ! [[ -f $XDEBUG_ENABLED_INI_FILE ]]; then
        exit
    fi

    cp "$XDEBUG_ENABLED_INI_FILE" "$XDEBUG_DISABLED_INI_FILE"
    phpenv config-rm xdebug.ini
}

#######################################
# Enables the Xdebug extension
#
# Globals:
#   XDEBUG_DISABLED_INI_FILE
#
# Arguments:
#   None
#
# Returns:
#   None
#######################################
function xdebug_enable() {
    if [[ -f $XDEBUG_DISABLED_INI_FILE ]]; then
        phpenv config-add $XDEBUG_DISABLED_INI_FILE
    fi
}

#######################################
# Gets the PR flags
#
# Globals:
#   TRAVIS_PULL_REQUEST
#   TRAVIS_BRANCH
#   MIN_MSI
#
# Arguments:
#   None
#
# Returns:
#   String
#######################################
function get_infection_pr_flags() {
    local flags="";
    local changed_files;
    local min_msi;

    if ! [[ "${TRAVIS_PULL_REQUEST}" == "false" ]]; then
        git remote set-branches --add origin "$TRAVIS_BRANCH";
        git fetch;

        changed_files=$(git diff origin/"$TRAVIS_BRANCH" --diff-filter=A --name-only | grep src/ | paste -sd "," -);
        min_msi=$(($(grep -o 'min-msi=[0-9]*' .travis.yml | head | cut -f2 -d=) + 1))

        >&2 echo "Assumed minimal MSI: $min_msi%"

        if [ -n "$changed_files" ]; then
            flags="--filter=${changed_files} --ignore-msi-with-no-mutations --min-msi=${min_msi} --show-mutations";
        fi
    fi

    echo "$flags";
}

# Restore this setting as Travis relies on that
# see https://github.com/travis-ci/travis-ci/issues/5434#issuecomment-438408950
set +u
