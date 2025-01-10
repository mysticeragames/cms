ARG BASE_VERSION=0.1.0
ARG BASE_IMAGE=mysticeragames/makeitstatic-cms-base
FROM ${BASE_IMAGE}:${BASE_VERSION}

# Switch to root user
USER root

# Set version
COPY ./.docker/cms/version /etc/cms-version

# Switch to cms user
USER cms

# Copy all CMS files
COPY --chown=cms ./ /var/www/html/

# Make sure the CMS runs in production mode
RUN echo -e "APP_ENV=prod\nAPP_SECRET=" > /var/www/html/.env.local

# Install all dependencies
RUN composer install --optimize-autoloader --no-interaction --no-progress

# Run post-install scripts
RUN composer run post-create-project-cmd

# Indicate which port needs to be connected with EXPOSE metadata
EXPOSE 8080

# Run supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Healthcheck
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping || exit 1
