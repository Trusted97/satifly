#syntax=docker/dockerfile:1

# 🧱 Base build syntax: Docker official syntax v1 for advanced features

# 🐘 Define base FrankenPHP version (PHP 8.4)
FROM dunglas/frankenphp:1-php8.4 AS frankenphp_upstream

# 📚 Info: multi-stage builds allow separate build and runtime images
# 🔗 Ref:
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target

# ⚙️ Stage 1: Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

# 📁 Set working directory
WORKDIR /app

# 💾 Create volume for runtime data (cache, logs, etc.)
VOLUME /app/var/

# 🧰 Install system dependencies
# hadolint ignore=DL3008
RUN apt-get clean && apt-get autoclean && apt-get update && apt-get install -y --no-install-recommends \
	file \
	git \
	&& rm -rf /var/lib/apt/lists/*

# 🛠️ Fix Git "dubious ownership" warning inside container
RUN git config --global --add safe.directory /app

# ⚙️ Install PHP extensions and Composer
RUN set -eux; \
	install-php-extensions \
		@composer \
		apcu \
		intl \
		opcache \
		zip \
	;

# 👑 Allow Composer to run as root
# 📖 https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# 🧩 Add app-specific PHP config directory
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

###> recipes ###
###< recipes ###

# 🧾 Copy custom PHP configuration, entrypoint, and Caddyfile
COPY --link frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link frankenphp/Caddyfile /etc/frankenphp/Caddyfile

# 🚪 Define entrypoint for container startup
ENTRYPOINT ["docker-entrypoint"]

# 🩺 Add healthcheck to verify Caddy/FrankenPHP is running
HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1

# ▶️ Default command: run FrankenPHP with provided config
CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile" ]

# 🧩 Stage 2: Dev image with Xdebug & hot-reload
FROM frankenphp_base AS frankenphp_dev

# 🌱 Development environment variables
ENV APP_ENV=dev
ENV XDEBUG_MODE=off
ENV FRANKENPHP_WORKER_CONFIG=watch

# 🧮 Use development PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# 🪄 Install Xdebug for debugging
RUN set -eux; \
	install-php-extensions \
		xdebug \
	;

# ⚙️ Copy development-specific PHP configuration
COPY --link frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

# ▶️ Run in watch mode for live reload
CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--watch" ]

# 🚀 Stage 3: Production image (optimized & minimal)
FROM frankenphp_base AS frankenphp_prod

# 🌍 Production environment variable
ENV APP_ENV=prod

# 🧮 Use production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# ⚙️ Copy production-specific PHP configuration
COPY --link frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

# 🚫 Prevent unnecessary vendor reinstall on rebuilds
COPY --link composer.* symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# 📦 Copy full source code
COPY --link . ./
RUN rm -Rf frankenphp/

# 🧰 Final app setup: build cache, autoload, and prepare Symfony
RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync;
