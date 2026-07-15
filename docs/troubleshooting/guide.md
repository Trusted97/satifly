# Troubleshooting Guide

Short fixes for common Satifly problems.

## Docker Will Not Start

| Symptom               | Likely cause                       | Fix                               |
|-----------------------|------------------------------------|-----------------------------------|
| Container exits early | Bad env or missing files           | Run `make doctor` and check logs  |
| Port already in use   | Another service uses `80` or `443` | Change `HTTP_PORT` / `HTTPS_PORT` |
| Permission errors     | Wrong owner on `var/` or `public/` | Run `make permissions`            |

## Composer Problems

| Symptom                    | Likely cause          | Fix                                          |
|----------------------------|-----------------------|----------------------------------------------|
| `composer install` fails   | Network or auth issue | Check credentials and network                |
| Build cannot find packages | Broken `satis.json`   | Validate config and rerun `make satis-build` |
| Cache seems stale          | Composer cache old    | Clear `COMPOSER_CACHE` or rebuild stack      |

## FrankenPHP Problems

| Symptom              | Likely cause          | Fix                           |
|----------------------|-----------------------|-------------------------------|
| App loads blank page | PHP fatal error       | Check container logs          |
| Build hangs          | Lock or process issue | Restart stack and rerun build |

## Caddy Problems

| Symptom       | Likely cause               | Fix                                     |
|---------------|----------------------------|-----------------------------------------|
| HTTPS warning | Self-signed cert           | Trust local cert or use production cert |
| 404 on route  | Wrong route or bad rewrite | Verify `frankenphp/Caddyfile`           |

## Asset Or UI Problems

| Symptom                     | Likely cause            | Fix                                                    |
|-----------------------------|-------------------------|--------------------------------------------------------|
| Styling missing             | Static asset not served | Check `public/css/bootstrap.min.css` and browser cache |
| JS collection controls fail | Script not loaded       | Confirm `collection-handler.js` included               |

## Build Failures

| Symptom              | Likely cause                     | Fix                                                  |
|----------------------|----------------------------------|------------------------------------------------------|
| Satis build fails    | Invalid config or package source | Inspect build output in UI and logs                  |
| Webhook does nothing | Bad token or payload             | Verify `X-Authentication-Token` and `repository.url` |

## Related Docs

- [Development guide](../development/guide.md)
- [Docker guide](../docker/overview.md)
- [Testing guide](../testing/guide.md)
- [Security guide](../security/overview.md)
