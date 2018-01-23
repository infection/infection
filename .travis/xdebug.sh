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