#!/bin/bash

mkdir -p ./reports/
rm -rf ./reports/*
XDEBUG_MODE=coverage php vendor/bin/phpunit --testdox-html ./reports/testdox.html --log-junit ./reports/junit.xml --log-events-text ./reports/log-events.txt --log-teamcity ./reports/log-teamcity.txt --log-events-verbose-text ./reports/log-events-verbose.txt --coverage-html ./reports/coverage

# Show reports (WSL2)
powershell.exe -c reports/coverage/index.html
powershell.exe -c reports/testdox.html
