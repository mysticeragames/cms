#!/bin/bash

# FROM ROOT FOLDER:   .cmd/run-build.sh

# Remove any old volume (if exists)
docker volume rm TEMP_makeitstatic_ssh

# Create volume for SSH keys (to access GIT repositories)
docker volume create TEMP_makeitstatic_ssh

# Copy SSH keys to volume and set owner
docker run --rm \
    -v TEMP_makeitstatic_ssh:/target \
    -v ~/.ssh:/source \
    alpine \
    sh -c "cp -r /source/* /target && chown -R 1111:1112 /target && ls -la /target"

# Start the CMS
docker run --rm \
    -p 8000:8250 \
    -v TEMP_makeitstatic_ssh:/home/appuser/.ssh:ro \
    --pull never \
    --name makeitstatic_cms_temp \
    cms:prod
