# Keep your registry up to date automatically

Satifly supports webhook integration so your package registry is refreshed automatically when repositories change. Supported providers:

*   GitHub
*   GitLab

## Webhook endpoint

Point your repository webhook to:

```
[your-satis-url]/webhook/package
```

Example:

```
https://satifly.example.com/webhook/package
```

## Payload

The webhook should POST JSON that includes the repository URL. Satifly will use that URL to fetch package metadata and update the registry.

## Authentication

Protect the endpoint by sending a secret token in the `X-Authentication-Token` header. Satifly will validate this token before accepting updates.

## Example (manual trigger / GitLab)

This `curl` request demonstrates how to POST to the webhook endpoint manually (or how GitLab can be configured to call it):

``` bash
curl -X \
 POST https://localhost/webhook/package \ -H \
 "Content-Type: application/json" \ -H \
 "X-Authentication-Token: your_secret_here" \ -d '{ "repository": { "url": "https://github.com/example/package" } }
```

## GitLab setup (quick)

1.  Open your GitLab repository → **Settings > Webhooks**.
2.  Set the URL to `https://<your-satis-url>/webhook/package`.
3.  Choose the trigger(s) you want (e.g., _Push events_).
4.  Optionally put your secret token in GitLab's **Secret Token** field and configure Satifly to expect that token in `X-Authentication-Token`.
5.  Save the webhook and test it.

## How it works

*   Provider (GitLab/GitHub) sends a POST to Satifly when the selected event occurs.
*   Satifly validates the `X-Authentication-Token`.
*   If valid, Satifly reads the repository URL from the payload, fetches metadata, and updates the registry.

## Security best practices

*   Always use HTTPS for your webhook URL.
*   Require and validate `X-Authentication-Token` on the endpoint.
*   Keep tokens secret and rotate them periodically.
