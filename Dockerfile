
# Dockerfile using multi-stage builds
# https://docs.docker.com/build/building/multi-stage/

# DOCKER_BUILDKIT=1 docker build --target minimal -t mysticeragames/local:minimal .
# DOCKER_BUILDKIT=1 docker build --target build_prod -t mysticeragames/local:build_prod .
# DOCKER_BUILDKIT=1 docker build --target build_test -t mysticeragames/local:build_test .

# DOCKER_BUILDKIT=1 docker build --target final_test -t mysticeragames/local:final_test .
# DOCKER_BUILDKIT=1 docker build --target final_prod -t mysticeragames/local:final_prod .

# see all images:   docker images mysticeragames/local
# run shell:        docker run --rm -it mysticeragames/local:minimal sh -c 'php -m && php -v && git -v'

# run phpcs:        docker run --rm -it mysticeragames/local:final_test php vendor/bin/phpcs
# run phpstan:      docker run --rm -it mysticeragames/local:final_test php vendor/bin/phpstan --memory-limit=512M analyse src tests
# run phpunit:      docker run --rm -it mysticeragames/local:final_test php vendor/bin/phpunit

# run test image:   docker run --rm -it -p 8250:8250 mysticeragames/local:final_test
# run production:   docker run --rm -it -p 8250:8250 mysticeragames/local:final_prod
# remove all:       docker rmi $(docker images -q mysticeragames/local:*)

################################################################################
# Minimal image
# This image should have the minimal requirements for the final production image
################################################################################

FROM alpine:3.21.2 AS minimal

# Set the php version (short format: 84)
ARG PHP_VERSION_SHORT=84

# Set the internal user/group
ARG APP_USER=appuser
ARG APP_GROUP=appgroup

# Set workdir
WORKDIR /var/www/html

# Add user with home dir (for git, that will be used to handle repositories)
RUN addgroup -g 1112 ${APP_GROUP} && \
    adduser -D -G ${APP_GROUP} -u 1111 -h /home/${APP_USER} -s /bin/sh ${APP_USER}

# Install packages
RUN apk add --no-cache \
    curl \
    git \
    nginx \
    php${PHP_VERSION_SHORT} \
    php${PHP_VERSION_SHORT}-ctype \
    php${PHP_VERSION_SHORT}-curl \
    php${PHP_VERSION_SHORT}-dom \
    #php${PHP_VERSION_SHORT}-fpm \
    php${PHP_VERSION_SHORT}-iconv \
    php${PHP_VERSION_SHORT}-mbstring \
    php${PHP_VERSION_SHORT}-openssl \
    php${PHP_VERSION_SHORT}-pecl-xdebug \
    php${PHP_VERSION_SHORT}-phar \
    php${PHP_VERSION_SHORT}-session \
    php${PHP_VERSION_SHORT}-simplexml \
    php${PHP_VERSION_SHORT}-tokenizer \
    php${PHP_VERSION_SHORT}-xml \
    php${PHP_VERSION_SHORT}-xmlwriter \
    php${PHP_VERSION_SHORT}-zip \
    supervisor

# GIT
RUN git config --global init.defaultBranch main && \
    git config --global --add safe.directory '*' && \
    git config --global user.email "MakeItStatic" &&\
    git config --global user.name "MakeItStatic"

# Nginx
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/conf.d /etc/nginx/conf.d/

# PHP
ARG PHP_INI_DIR=/etc/php${PHP_VERSION_SHORT}
COPY .docker/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY .docker/php.ini ${PHP_INI_DIR}/conf.d/custom.ini
RUN if [ ! -f /usr/bin/php ]; then ln -s /usr/bin/php${PHP_VERSION_SHORT} /usr/bin/php; fi

# Supervisor
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN sed -i "s/php-fpm___VERSION___/php-fpm${PHP_VERSION_SHORT}/g" /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R ${APP_USER}:${APP_GROUP} /var/www/html /run /var/lib/nginx /var/log/nginx


# TODO:   RUN composer run post-create-project-cmd


################################################################################
# Composer production
# This will install composer dependencies for production (without 'require-dev')
################################################################################

FROM minimal AS build_prod
COPY --from=composer/composer:2.8.5-bin /composer /usr/bin/composer
COPY composer.* .
RUN composer install --prefer-dist --no-interaction --optimize-autoloader --no-progress --no-scripts --no-dev



################################################################################
# Composer test
# This will install composer dependencies for testing (including 'require-dev')
################################################################################

FROM build_prod AS build_test
COPY --from=build_prod /var/www/html/vendor vendor
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress --no-scripts



################################################################################
# Final production image
# This will be the final production image
################################################################################

FROM minimal AS final_prod

# Copy all source files
COPY --chown=${APP_USER}:${APP_GROUP} . /var/www/html

# Copy all vendor files
COPY --chown=${APP_USER}:${APP_GROUP} --from=build_prod /var/www/html/vendor vendor

# Make sure it runs in production mode
RUN echo -e "APP_ENV=prod\nAPP_SECRET=" > /var/www/html/.env.local

# Setup directories and file permissions https://symfony.com/doc/current/setup/file_permissions.html
RUN mkdir -p ./var/log ./var/cache && \
    chown -R ${APP_USER}:${APP_GROUP} ./var ./.env.local

USER ${APP_USER}
EXPOSE 8250
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
HEALTHCHECK --timeout=10s --interval=5s --start-interval=2s CMD curl --silent --fail http://127.0.0.1:8250/fpm-ping || exit 1



################################################################################
# Final test image
# This will be the final test image
################################################################################

FROM minimal AS final_test

# Install packages
RUN apk add --no-cache \
    php${PHP_VERSION_SHORT}-pecl-xdebug \
    php${PHP_VERSION_SHORT}-phar
    #chromium-chromedriver

# Use test config
COPY .docker/test/99-xdebug.ini ${PHP_INI_DIR}/conf.d/99-xdebug.ini

# Copy all source files
COPY --chown=${APP_USER}:${APP_GROUP} . /var/www/html

# Copy all vendor files
COPY --chown=${APP_USER}:${APP_GROUP} --from=build_test /var/www/html/vendor vendor

# Make sure it runs in test mode
COPY .env.test .env.local

# Setup directories and file permissions https://symfony.com/doc/current/setup/file_permissions.html
RUN mkdir -p ./var/log ./var/cache && \
    chown -R ${APP_USER}:${APP_GROUP} ./var ./.env.local

USER ${APP_USER}
EXPOSE 8250
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
HEALTHCHECK --timeout=10s --interval=5s --start-interval=2s CMD curl --silent --fail http://127.0.0.1:8250/fpm-ping || exit 1
