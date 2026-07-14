# ===========================================
# 🧩 Satifly - Developer Makefile
# Simplifies common Docker & Symfony tasks
# ===========================================
.SILENT:

# Default variables (can be overridden)
APP_NAME       ?= satifly
APP_PORT       ?= 80
COMPOSE        ?= docker compose
PHP_CONTAINER  ?= php
ENV_FILE       ?= .env

# Colors
YELLOW=\033[1;33m
GREEN=\033[1;32m
CYAN=\033[1;36m
RESET=\033[0m

# -------------------------------------------
# 🏁 Help
# -------------------------------------------
.PHONY: help
help:
	@echo ""
	@echo "$(CYAN)Satifly Developer Commands$(RESET)"
	@echo "-------------------------------------------"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-20s$(RESET) %s\n", $$1, $$2}'
	@echo ""

# -------------------------------------------
# ⚙️ Docker Commands
# -------------------------------------------

build: ## Build Docker images (no cache, pull latest)
	$(COMPOSE) build --pull --no-cache

up: ## Start containers and wait until ready
	$(COMPOSE) up --wait

down: ## Stop and remove containers, networks, and volumes
	$(COMPOSE) down --remove-orphans

restart: down up ## Restart the Docker stack

logs: ## Tail container logs
	$(COMPOSE) logs -f

ps: ## Show running containers
	$(COMPOSE) ps

clean: ## Remove containers, images, and volumes completely
	$(COMPOSE) down --rmi all --volumes --remove-orphans

rebuild: clean build up ## Full rebuild of the environment

# -------------------------------------------
# 🧰 App & Composer Commands
# -------------------------------------------

install: ## Install PHP dependencies via Composer
	$(COMPOSE) exec $(PHP_CONTAINER) composer install -n --prefer-dist

update: ## Update Composer dependencies
	$(COMPOSE) exec $(PHP_CONTAINER) composer update

shell: ## Access the PHP container shell
	$(COMPOSE) exec $(PHP_CONTAINER) bash

test: ## Run PHPUnit tests
	$(COMPOSE) exec $(PHP_CONTAINER) composer test

style: ## Run PHP-CS-Fixer
	$(COMPOSE) exec $(PHP_CONTAINER) composer php-cs-fixer

satis-init: ## Initialize satis.json interactively
	$(COMPOSE) exec $(PHP_CONTAINER) vendor/bin/satis init

satis-build: ## Build satis packages
	$(COMPOSE) exec $(PHP_CONTAINER) vendor/bin/satis build

# -------------------------------------------
# 🧹 Utilities
# -------------------------------------------

permissions: ## Fix file permissions for storage and cache
	$(COMPOSE) exec $(PHP_CONTAINER) chown -R www-data:www-data /app/var /app/public

env-check: ## Ensure .env file exists
	@if [ ! -f $(ENV_FILE) ]; then \
		echo "$(YELLOW)[WARN]$(RESET) No .env file found. Copying .env.dist..."; \
		cp .env.dist .env; \
	fi

doctor: ## Run project health checks
	$(COMPOSE) exec $(PHP_CONTAINER) sh -c '\
		for dir in /app/var/composer /app/var/composer/cache; do \
			if [ ! -d $$dir ]; then \
				echo "📁 Creating missing directory $$dir..."; \
				mkdir -p $$dir; \
			fi; \
		done && \
		php bin/console about'

# -------------------------------------------
# 🪄 Xdebug Controls
# -------------------------------------------

xdebug-on: ## Enable Xdebug in the PHP container
	@echo "$(CYAN)🔍 Enabling Xdebug...$(RESET)"
	$(COMPOSE) exec $(PHP_CONTAINER) bash -c "touch /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && echo 'zend_extension=xdebug' > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
	$(COMPOSE) exec $(PHP_CONTAINER) bash -c "echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
	$(COMPOSE) exec $(PHP_CONTAINER) bash -c "echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
	$(COMPOSE) restart $(PHP_CONTAINER)
	@echo "$(GREEN)✔ Xdebug enabled!$(RESET)"

xdebug-off: ## Disable Xdebug in the PHP container
	@echo "$(YELLOW)🧹 Disabling Xdebug...$(RESET)"
	$(COMPOSE) exec $(PHP_CONTAINER) bash -c "rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
	$(COMPOSE) restart $(PHP_CONTAINER)
	@echo "$(GREEN)✔ Xdebug disabled!$(RESET)"

xdebug-connect: xdebug-on ## Enable Xdebug and trigger PhpStorm debugger
	@echo "$(CYAN)🌐 Sending test request to trigger Xdebug...$(RESET)"
	@curl -s -o /dev/null "http://localhost" -H "XDEBUG_SESSION=PHPSTORM"
	@echo "$(GREEN)✔ Test request sent. Make sure PhpStorm is listening for debug connections!$(RESET)"

xdebug-help: ## Show PhpStorm + Docker + Xdebug configuration instructions
	@echo "$(CYAN)🛠️ PhpStorm + Docker + Xdebug Setup Instructions$(RESET)"
	@echo "1. Open PhpStorm Settings: press ⌘ Cmd + 0 (macOS) or File → Settings (Windows/Linux) → PHP."
	@echo "2. On the PHP page, click the Browse button next to the CLI Interpreter list."
	@echo "3. In the CLI Interpreters dialog, click the '+' button → choose 'From Docker, Vagrant, VM, WSL, Remote...'."
	@echo "4. In the 'Configure Remote PHP Interpreter' dialog, select the Docker method."
	@echo "5. Provide the Docker connection parameters:"
	@echo "     • Server: select your Docker configuration, or click 'New...' to add one."
	@echo "     • Image name: specify the base PHP image, e.g., php:latest or php:8.2-cli."
	@echo "     • PHP interpreter path: PhpStorm usually suggests /usr/local/bin/php inside the container."
	@echo "6. Click OK: PhpStorm checks if the PHP executable exists inside the container."
	@echo "     • If found, the CLI Interpreters dialog will show the installation folder and PHP version."
	@echo "     • Click 'Show phpinfo' to see loaded extensions and configured options."
	@echo "7. Make sure Xdebug is installed in your container (use 'make xdebug-on')."
	@echo "8. In PhpStorm toolbar, click the phone icon to 'Start Listening for PHP Debug Connections'."
	@echo "9. Configure path mappings: map your local project folder → remote folder inside the container (e.g., /var/www/html)."
	@echo "10. Trigger a debug session using 'make xdebug-connect' or by appending ?XDEBUG_SESSION=PHPSTORM to your URL."

# -------------------------------------------
# 🚀 Shortcuts
# -------------------------------------------

start: env-check build up install ## Quick start: build, run, and install dependencies
	@echo "$(GREEN)✔ Satifly is now running at https://localhost:$(APP_PORT)$(RESET)"

stop: down ## Stop the environment
	@echo "$(YELLOW)✋ Environment stopped.$(RESET)"
