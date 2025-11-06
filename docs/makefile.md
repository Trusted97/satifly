# Satifly — Makefile Documentation

This document describes the `Makefile` provided for the **Satifly** project. It explains variables, available targets, examples, and Xdebug toggles. 
The file aims to simplify common Docker, Composer, and Symfony workflows.

## Quick start

Common quick commands:

*   `make help` — show available commands
*   `make start` — build images, start containers, install dependencies
*   `make stop` — stop and remove containers
*   `make shell` — open a bash shell inside the PHP container

## Environment variables (overridable)

These variables are defined in the Makefile and can be overridden on the command line or in your environment.

*   `APP_NAME` — application name (default: `satifly`)
*   `APP_PORT` — HTTP port forwarded from the container (default: `8000`)
*   `COMPOSE` — docker compose command (default: `docker compose`)
*   `PHP_CONTAINER` — name of the PHP service/container in docker-compose (default: `php`)
*   `ENV_FILE` — path to environment file checked by `make env-check` (default: `.env`)

## How to override

Example: run make with a different port:

```
APP_PORT=8080 make start
```

## Targets (commands)

The Makefile exposes the following main targets. Use `make help` inside the repo to get a live list.

### Docker / lifecycle

*   `build` — Build Docker images (pull latest, no cache).
*   `up` — Start containers and wait until ready.
*   `down` — Stop and remove containers, networks and orphaned services.
*   `restart` — Equivalent to `make down` then `make up`.
*   `logs` — Tail service logs (`docker compose logs -f`).
*   `ps` — Show running containers (`docker compose ps`).
*   `clean` — Remove containers, images and volumes: `docker compose down --rmi all --volumes --remove-orphans`.
*   `rebuild` — Full teardown + build + up (runs `clean`, `build`, then `up`).

### Application / Composer

*   `install` — Run `composer install -n --prefer-dist` inside the PHP container.
*   `update` — Run `composer update` inside the PHP container.
*   `shell` — Open an interactive bash shell in the PHP container: `docker compose exec <php> bash`.
*   `test` — Run PHPUnit tests: `php bin/phpunit` inside container.
*   `satis-init` — Run the interactive `vendor/bin/satis init` command.
*   `satis-build` — Run `vendor/bin/satis build` to generate package definitions.

### Utilities

*   `permissions` — Fix file ownership/permissions for `var` and `public` folders.
*   `env-check` — Ensure an `.env` file exists; copies from `.env.dist` if missing.
*   `doctor` — Run a Symfony environment/info check (example: `php bin/console about`).

### Xdebug controls

Convenience targets to enable/disable Xdebug inside the PHP container at runtime. These targets assume the container uses a standard PHP configuration directory (`/usr/local/etc/php/conf.d`).

*   `xdebug-on` — Create a small config file enabling Xdebug and restart the PHP container. It writes:

    ```
    zend_extension=xdebug xdebug.mode=debug xdebug.start_with_request=yes
    ```

    Then restarts the PHP container to apply changes.
*   `xdebug-off` — Remove the Xdebug config file and restart the PHP container.

## Examples & common workflows

### Start developing (first time)

```
# Build, start and install deps make start
Open the app
https://localhost:8000
 (or the port set with APP_PORT)
```

### Enable Xdebug for a debug session

```
# Turn Xdebug on make xdebug-on
Attach your debugger (IDE) to the forwarded port / container
When finished, turn it off:

make xdebug-off
```

### Rebuild everything

```
# Full clean + rebuild make rebuild
```

## Troubleshooting

*   **Composer hanging or failing:** ensure `auth.json` is present if you access private repos. Use `make shell` and run composer manually inside the container to inspect errors.
*   **Missing .env:** run `make env-check` or copy `.env.dist` to `.env`.
*   **Xdebug not connecting:** check `xdebug.client_host` (you may need to set it to `host.docker.internal` or your host IP). See the _Notes on Xdebug host configuration_ below.

## Notes on Xdebug host configuration

The Makefile toggles Xdebug on/off but does not set `xdebug.client_host`. For reliable IDE connections on macOS/Windows use `host.docker.internal`. On Linux you may need to set the host IP manually. To add automatic detection, you can modify the `xdebug-on` target to append a line like:

```
echo "xdebug.client_host=$(HOST_IP)" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
```

Where `HOST_IP` can be computed dynamically (e.g. via `ip route get 1` or supplied as an environment variable).

## Authoring tips & customization

*   You can add more targets to run common `bin/console` commands (migrations, cache:clear, doctrine commands, etc.).
*   If your PHP container has a different name in `docker-compose.yml`, set `PHP_CONTAINER` when invoking make, e.g. `PHP_CONTAINER=app_php make shell`.
*   To add environment-specific behavior (CI vs local), you can create `Makefile.ci` and include it or add conditionals that read `APP_ENV`.
