#!/usr/bin/env bash

docker run -t -v "$PWD":/opt -w /opt php:8.1-alpine vendor/bin/infection --coverage=infection-coverage

diff -w expected-output.txt infection.log
