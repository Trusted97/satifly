# Webhook Guide

Quick webhook reference. API detail lives in [docs/api/webhook.md](api/webhook.md).

## Supported Providers

- GitHub
- GitLab

## Endpoint

```text
POST /webhook/package
```

## Required Header

```text
X-Authentication-Token: <PACKAGE_SECRET>
```

## Payload

Webhook body must include:

```json
{
  "repository": {
    "url": "https://github.com/example/package.git"
  }
}
```

## Example

```bash
curl -X POST https://localhost/webhook/package \
  -H "Content-Type: application/json" \
  -H "X-Authentication-Token: your_secret_here" \
  -d '{"repository":{"url":"https://github.com/example/package.git"}}'
```

## Behavior

- Valid token and payload dispatch build event.
- Missing token returns `401`.
- Missing repository payload returns `400`.

## Related Docs

- [Webhook API](api/webhook.md)
- [Security overview](security/overview.md)
