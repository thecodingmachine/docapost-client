#!/usr/bin/env bash

cd /home/docker
composer create-project thecodingmachine/washingmachine washingmachine ^2.0 --stability=dev
cd -

# Register token
composer config --global github-oauth.github.com "$GITHUB_ACCESS_TOKEN"

composer install --prefer-dist
