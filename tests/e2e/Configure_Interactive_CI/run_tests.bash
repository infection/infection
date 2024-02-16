#!/usr/bin/env bash

readonly INFECTION="../../../bin/infection"

export TRAVIS=true

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

if [ $? == 0 ]
then
    test -x $(which tput) && tput setaf 1 # red
    echo "Infection didn't fail when not configured during CI."
    
    exit 1
fi
