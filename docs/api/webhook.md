# Webhook API

Satifly exposes one webhook endpoint for repository refresh.

## Endpoint

```http
POST /webhook/package
```

Route is wired through Symfony Webhook routing and handled by `App\RemoteEvent\PackageWebhookConsumer`.

## Authentication

Send secret token in header:

```http
X-Authentication-Token: <PACKAGE_SECRET>
```

Secret value comes from `PACKAGE_SECRET`.

## Request Body

Payload must include a `repository` object with `url`.

Example:

```json
{
  "repository": {
    "url": "https://github.com/acme/package.git"
  }
}
```

## Success Flow

1. Request parser validates method and token.
2. Payload is parsed into `RemoteEvent`.
3. Consumer looks up repository by URL.
4. `BuildEvent` dispatches.
5. `SatisManager` runs build for matched repository.

## Failure Modes

| Status | Cause                        |
|--------|------------------------------|
| `401`  | Bad auth token               |
| `400`  | Missing `repository` payload |

## Example `curl`

```bash
curl -X POST https://localhost/webhook/package \
  -H "Content-Type: application/json" \
  -H "X-Authentication-Token: your-secret" \
  -d '{"repository":{"url":"https://github.com/acme/package.git"}}'
```

## Related Docs

- [Webhook guide](../webhook.md)
- [Security guide](../security/overview.md)
- [Architecture overview](../architecture/overview.md)
