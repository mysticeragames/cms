# SOURCE: https://github.com/TrafeX/docker-php-nginx


# Set base image
ARG ALPINE_VERSION=3.21
FROM alpine:${ALPINE_VERSION}

# Set working directory
WORKDIR /var/www/html

# Add cms user with home dir (for git)
RUN addgroup -g 1000 cmsg && \
    adduser -D -G cmsg -u 1000 -h /home/cms -s /bin/sh cms

# Install packages
RUN apk add --no-cache \
    bash \
    curl \
    nginx \
    git \
    npm \
    php83 \
    php83-ctype \
    php83-curl \
    php83-dom \
    php83-fileinfo \
    php83-fpm \
    php83-gd \
    php83-iconv \
    php83-intl \
    php83-mbstring \
    php83-mysqli \
    php83-opcache \
    php83-openssl \
    php83-phar \
    php83-session \
    php83-tokenizer \
    php83-xml \
    php83-xmlreader \
    php83-xmlwriter \
    supervisor

# Nginx
COPY .docker/nginx.conf /etc/nginx/nginx.conf
#COPY .docker/conf.d /etc/nginx/conf.d/
COPY .docker/conf.d-symfony /etc/nginx/conf.d/

# PHP
ENV PHP_INI_DIR=/etc/php83
COPY .docker/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY .docker/php.ini ${PHP_INI_DIR}/conf.d/custom.ini
RUN if [ ! -f /usr/bin/php ]; then ln -s /usr/bin/php83 /usr/bin/php; fi

# Supervisor
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Composer
# https://getcomposer.org/doc/00-intro.md#docker-image
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

# Set permissions
RUN chown -R cms:cmsg /var/www/html /run /var/lib/nginx /var/log/nginx

# Switch to cms-user
USER cms

# Setup git
RUN git config --global init.defaultBranch main && \
    git config --global user.email "MakeItStatic" &&\
    git config --global user.name "MakeItStatic"

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
