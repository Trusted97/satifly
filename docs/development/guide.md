# Developer Guide

This guide is for new contributors and maintainers.

## Prerequisites

- PHP 8.4
- Composer 2
- Docker Compose v2
- `make`

## Quick Start

```bash
git clone https://github.com/Trusted97/satifly
cd satifly
make build
make up
make doctor
```

Open `https://localhost`.

## Common Commands

| Command            | What it does             |
|--------------------|--------------------------|
| `make help`        | Show all targets         |
| `make up`          | Start stack              |
| `make down`        | Stop stack               |
| `make rebuild`     | Full clean rebuild       |
| `make test`        | Run PHPUnit              |
| `make style`       | Run PHP-CS-Fixer         |
| `make shell`       | Open PHP container shell |
| `make satis-init`  | Create `satis.json`      |
| `make satis-build` | Run Satis build          |
| `make doctor`      | Check app health         |

## Composer Commands

- `composer install`
- `composer update`
- `composer test`
- `composer php-cs-fixer`

Use host Composer only when you know the container is not needed. Container environment matches production better.

## Debugging

- Enable Xdebug with `make xdebug-on`
- Disable it with `make xdebug-off`
- On Linux, set IDE host manually if needed

## Cache And Assets

- Symfony cache lives under `var/`
- Static assets live in `public/`
- No AssetMapper or Node pipeline today
- Bootstrap CSS/JS is vendored under `public/`

## Local Workflow

1. Start stack.
2. Edit config, controllers, Twig, or services.
3. Run targeted tests.
4. Run `make test` before push.
5. Use `make rebuild` after deep dependency changes.

## Commit Style

- Commits: conventional commits
- Keep changes small and reviewable

## Related Docs

- [Architecture overview](../architecture/overview.md)
- [Docker guide](../docker/overview.md)
- [Testing guide](../testing/guide.md)
- [Troubleshooting guide](../troubleshooting/guide.md)
