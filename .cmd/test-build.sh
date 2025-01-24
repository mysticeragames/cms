#!/bin/bash

# FROM ROOT FOLDER:   .cmd/test-build.sh

# Notes:
# - the build test container is being tested, not your local changes!
# For local testing, just run directly: vendor/bin/phpunit
# For building your local changes, and testing the actual build, run: ./.cmd/build-and-test.sh
docker run --rm -it --name test-phpunit cms:test php vendor/bin/phpunit || exit 1
docker run --rm -it --name test-phpstan cms:test php vendor/bin/phpstan --memory-limit=512M analyse src tests || exit 1
docker run --rm -it --name test-phpcs cms:test php vendor/bin/phpcs || exit 1

# See if the containers actually serve content (this is also in job.docker-image.yml)
# Send to the background with '&' at the end so they run in parallel, and wait for them to complete with 'wait'
.cmd/check-container.sh cms:prod 8299 &
.cmd/check-container.sh cms:test 8298 &

# Wait for background processes to complete
FAILURES=0
for job in `jobs -p`; do
    wait $job || let "FAILURES+=1"
done

# Exit on one or more failures
if [ "$FAILURES" != "0" ];
then
    echo "FAILURE (${FAILURES}x)"
    exit 1
fi

# display all images:
docker images cms:*
