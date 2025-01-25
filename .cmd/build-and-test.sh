#!/bin/bash

# FROM ROOT FOLDER:   .cmd/build-and-test.sh

# Build images
.cmd/only-build.sh

# Test images
.cmd/test-build.sh


# Look in image:
# docker run --rm -it cms:test cat tests/Functional/Controller/SiteControllerTest.php
