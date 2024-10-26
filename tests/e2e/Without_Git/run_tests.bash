#!/usr/bin/env bash

set -e

echo "GITHUB_HEAD_REF=$GITHUB_HEAD_REF";
echo "GITHUB_REPOSITORY=$GITHUB_REPOSITORY";
echo "$(env)"

git_branch=$(echo "${GITHUB_HEAD_REF:-$(git rev-parse --abbrev-ref HEAD)}" | sed 's/\//\\\//g')

echo "git_branch: ${git_branch}"

if [ "$git_branch" == "master" ]; then
  exit 0;
fi;

sed -i "s/\"infection\/infection\": \"dev-master\"/\"infection\/infection\": \"dev-${git_branch}\"/" composer.json

set -e pipefail

rm -f composer.lock
composer install

docker run -t -v "$PWD":/opt -w /opt php:8.1-alpine vendor/bin/infection --coverage=infection-coverage

diff -w expected-output.txt infection.log
