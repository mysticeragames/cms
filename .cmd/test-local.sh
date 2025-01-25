#!/bin/bash

# FROM ROOT FOLDER:   .cmd/test-local.sh

docker run --rm -it -u $(id -u):$(id -g) -v $(pwd):/var/www/html --name test-phpunit cms:test sh -c "php -d xdebug.mode=coverage vendor/bin/phpunit --coverage-html ./reports/coverage" || exit 1
docker run --rm -it -u $(id -u):$(id -g) -v $(pwd):/var/www/html --name test-phpstan cms:test sh -c "php vendor/bin/phpstan --memory-limit=512M analyse src tests" || exit 1
docker run --rm -it -u $(id -u):$(id -g) -v $(pwd):/var/www/html --name test-phpcs cms:test sh -c "php vendor/bin/phpcs" || exit 1
