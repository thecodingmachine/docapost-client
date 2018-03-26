#!/usr/bin/env bash

FAIL=0
export FILES_TO_MESSAGE=''

cd app
phpdbg -qrr vendor/bin/phpunit
if [ $? -ne 0 ]; then
    let "FAIL+=1"
fi

# When piping commands (like tee), we want the original error code to be propagated.
set -o pipefail

composer phpstan | tee phpstan_results.txt
if [ $? -ne 0 ]; then
    let "FAIL+=1"
    export FILES_TO_MESSAGE="$FILES_TO_MESSAGE -f phpstan_results.txt"
fi

composer cs-check | tee php-cs-fixer_results.txt
if [ $? -ne 0 ]; then
    let "FAIL+=1"
    export FILES_TO_MESSAGE="$FILES_TO_MESSAGE -f php-cs-fixer_results.txt"
fi

/home/docker/washingmachine/washingmachine run -v $FILES_TO_MESSAGE

exit ${FAIL}
