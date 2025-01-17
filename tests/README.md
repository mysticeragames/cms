# Unit Tests

These PHPUnit tests (+ additional testing tools) are run in the gitlab actions on each pull-request, merge/push to main, and on each release.

Run all required tests:

```bash
composer test

# On errors, try to run:
composer fix
```

## PHPUnit commands

```bash
# Run all tests
vendor/bin/phpunit

# List all testsuites
vendor/bin/phpunit --list-suites

# Run testsuite
vendor/bin/phpunit --testsuite unit

# List all tests
vendor/bin/phpunit --list-tests

# List all tests for a class
vendor/bin/phpunit --filter PageRepositoryTest --list-tests

# Run all tests for a class
vendor/bin/phpunit --filter PageRepositoryTest

# RUn all tests for a class in a specific testsuite
vendor/bin/phpunit --filter PageRepositoryTest --testsuite integration

# Run only 1 test inside a class in a specific testsuite
vendor/bin/phpunit --filter PageRepositoryTest::testGetPages --testsuite integration
```
