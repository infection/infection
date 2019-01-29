#!/usr/bin/env bash

# See
#
# - https://docs.travis-ci.com/user/languages/php#Disabling-preinstalled-PHP-extensions
# - https://docs.travis-ci.com/user/languages/php#Custom-PHP-configuration

config="/home/travis/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini"

function xdebug-disable() {
    if [[ -f $config ]]; then
        cp $config /tmp
        phpenv config-rm xdebug.ini
    fi
}

function xdebug-enable() {
    if [[ -f "/tmp/xdebug.ini" ]]; then
        phpenv config-add /tmp/xdebug.ini
    fi
}

function get-infection-pr-flags() {
    if [[ "${TRAVIS_PULL_REQUEST}" == "false" ]]; then
        INFECTION_PR_FLAGS="";
    else
        git remote set-branches --add origin $TRAVIS_BRANCH;
        git fetch;

        CHANGED_FILES=$(git diff origin/$TRAVIS_BRANCH --diff-filter=AM --name-only | grep src/ | paste -sd "," -);
        INFECTION_PR_FLAGS="--filter=${CHANGED_FILES} --ignore-msi-with-no-mutations --only-covered --min-msi=90";
    fi

    echo $INFECTION_PR_FLAGS;
}
