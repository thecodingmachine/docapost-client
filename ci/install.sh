#!/usr/bin/env bash

cd /home/docker
composer create-project thecodingmachine/washingmachine washingmachine ^2.0 --stability=dev
cd -

composer install --prefer-dist
