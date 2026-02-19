#!/usr/bin/env bash

cd "$(dirname "$0")"

set -e

# PRs from forked repositories are ignored cause we can't use such branches from another remote in composer.json below
if [[ "$GITHUB_EVENT_NAME" == "pull_request" ]]; then
  # Extract the repo full names from the event payload
  pr_repo_full_name=$(jq -r .pull_request.head.repo.full_name "$GITHUB_EVENT_PATH")
  base_repo_full_name=$(jq -r .repository.full_name "$GITHUB_EVENT_PATH")

  if [[ "$pr_repo_full_name" != "$base_repo_full_name" ]]; then
    echo "This pull request is from a forked repository."
    exit 0
  fi
fi

git_branch=$(echo "${GITHUB_HEAD_REF:-$(git rev-parse --abbrev-ref HEAD)}" | sed 's/\//\\\//g')

echo "git_branch: ${git_branch}"

if [ "$git_branch" == "master" ]; then
  exit 0;
fi;

sed -i "s/\"infection\/infection\": \"dev-master\"/\"infection\/infection\": \"dev-${git_branch}\"/" composer.json

set -e pipefail

rm -f composer.lock

# For local testing on forks, detect the fork's remote URL and add it as a repository
# This allows Composer to find branches that exist on the fork but not on the main repo
if [ -z "$GITHUB_EVENT_NAME" ]; then
  fork_url=$(git -C ../../.. remote get-url origin 2>/dev/null || true)
  if [ -n "$fork_url" ]; then
    composer config repositories.fork vcs "$fork_url" --no-interaction
  fi
fi

composer install

docker run -t -v "$PWD":/opt -w /opt php:8.4-alpine vendor/bin/infection --coverage=infection-coverage

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt infection.log
