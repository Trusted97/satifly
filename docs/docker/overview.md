# Docker Guide

Satifly runs on FrankenPHP plus Caddy.

## Container Layout

| File                              | Role                              |
|-----------------------------------|-----------------------------------|
| `compose.yaml`                    | Base runtime and ports            |
| `compose.override.yaml`           | Dev build target and bind mounts  |
| `compose.prod.yaml`               | Production composition            |
| `Dockerfile`                      | FrankenPHP image build            |
| `frankenphp/Caddyfile`            | Web server and PHP worker config  |
| `frankenphp/docker-entrypoint.sh` | First-run setup and startup logic |

## Important Paths

- App root: `/app`
- Public web root: `/app/public`
- Runtime cache: `/app/var`
- Composer home: `/app/var/composer`

## Environment Variables

| Variable         | Purpose                    |
|------------------|----------------------------|
| `SERVER_NAME`    | Caddy server name          |
| `APP_ENV`        | Symfony environment        |
| `SATIS_CONFIG`   | Path to Satis JSON         |
| `SATIS_LOG`      | Satis backup/log directory |
| `COMPOSER_HOME`  | Composer home directory    |
| `COMPOSER_CACHE` | Composer cache directory   |
| `PACKAGE_SECRET` | Webhook secret             |

## Dev Workflow

```bash
make build
make up
make shell
```

Mounts in `compose.override.yaml` give live code reload in dev.

## Production Notes

- Production image builds app into container.
- Worker mode keeps request handling fast.
- Caddy serves HTTPS and compression.
- Cache and config files must be writable by container user.

## Troubleshooting

- `make permissions` if `var/` or `public/` becomes unwritable.
- `make doctor` if container starts but app fails.
- `docker compose logs -f` for boot or build issues.

## Related Docs

- [Development guide](../development/guide.md)
- [Troubleshooting guide](../troubleshooting/guide.md)
- [Security guide](../security/overview.md)
