#!/bin/bash

# FROM ROOT FOLDER:   .cmd/coverage.sh

mkdir -p ./reports/
rm -rf ./reports/*
XDEBUG_MODE=coverage php vendor/bin/phpunit --testdox-html ./reports/testdox.html --log-junit ./reports/junit.xml --log-events-text ./reports/log-events.txt --log-teamcity ./reports/log-teamcity.txt --log-events-verbose-text ./reports/log-events-verbose.txt --coverage-html ./reports/coverage
