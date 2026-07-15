# Security Guide

Satifly security model is simple on purpose.

## Authentication

- Admin UI can be protected with `admin.auth`
- Auth users live in `config/parameters.yml`
- Symfony form login uses in-memory users in tests

## Authorization

- Protected controllers extend `AbstractProtectedController`
- When `admin.auth` is on, `ROLE_ADMIN` is required
- When auth is off, admin area is open

## Webhook Security

- Webhook route: `POST /webhook/package`
- Secret comes from `PACKAGE_SECRET`
- Client must send `X-Authentication-Token`
- Payload must include `repository.url`

## CSRF

- Admin forms use Symfony form protection
- Login uses Symfony security form login
- Delete action uses a DELETE form

## Headers And Transport

- Caddy enables HTTPS
- Basic hardening headers are set in `frankenphp/Caddyfile`
- Use HTTPS for every deployment

## Secrets

Keep these out of git:

- `APP_SECRET`
- `PACKAGE_SECRET`
- Composer auth credentials if used

## Operational Advice

- Rotate webhook token on compromise
- Keep `admin.auth` enabled in shared or public environments
- Limit access to `SATIS_CONFIG` and `SATIS_LOG`

## Related Docs

- [Architecture overview](../architecture/overview.md)
- [Docker guide](../docker/overview.md)
- [Webhook API](../api/webhook.md)
