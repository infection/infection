#!/bin/bash
    if [ "${SYMFONY_VERSION}" != "" ]; then
      composer config --unset platform.php # removes php 7.0.8 platform fake requirement
    fi