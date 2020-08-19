#!/usr/bin/env bash


readonly INFECTION="../../../bin/infection --min-msi=100"

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

if [ $? == 0 ]
then
    test -x $(which tput) && tput setaf 1 # red
    echo "Infection didn't fail when MSI was lower than expected."
    
    exit 1
fi
