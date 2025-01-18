# Unit Tests

These PHPUnit tests (+ additional testing tools) are run in the gitlab actions on each pull-request, merge/push to main, and on each release.

Run all required tests:

```bash
composer test

# On errors, try to run:
composer fix
```

## Requirements

- These requirements are checked automatically by `AllTestsRequireSpecificSetupTest.php`
- All tests should extend one of these base classes: `BaseUnitTestCase`, `BaseIntegrationTestCase`, or `BaseFunctionalTestCase`
- All tests should start with a very specific comment, to quickly start the test when looking at the file:
    ```php
    <?php

    /*

    vendor/bin/phpunit --testsuite unit --filter DateHelperTest
    vendor/bin/phpunit --testsuite unit --filter DateHelperTest testMyMethod

    */
    ```

## PHPUnit commands

```bash
# Run all tests
vendor/bin/phpunit

# List all testsuites
vendor/bin/phpunit --list-suites

# Run testsuite
vendor/bin/phpunit --testsuite unit
vendor/bin/phpunit --testsuite integration
vendor/bin/phpunit --testsuite functional

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
