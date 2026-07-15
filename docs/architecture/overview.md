# Architecture Overview

Satifly is a file-based Symfony app around Satis. No database. No queue. No frontend build pipeline. That is on purpose.

## What App Does

- Stores Satis config in JSON file.
- Lets admin users edit repositories in web UI.
- Imports repository URLs from `composer.lock`.
- Triggers Satis builds through a Symfony command and build listener.
- Accepts webhook events for repository updates.

## Project Shape

| Area                    | Main code                                    |
|-------------------------|----------------------------------------------|
| Admin UI                | `src/Controller/AdminController.php`         |
| Satis UI and build page | `src/Controller/SatisController.php`         |
| Auth                    | `src/Controller/SecurityController.php`      |
| File config             | `src/Persister/FilePersister.php`            |
| JSON serialization      | `src/Persister/JsonPersister.php`            |
| Repository logic        | `src/Service/RepositoryManager.php`          |
| Satis build flow        | `src/Service/SatisManager.php`               |
| Lock import             | `src/Service/LockProcessor.php`              |
| Build event             | `src/Event/BuildEvent.php`                   |
| Webhook consumer        | `src/RemoteEvent/PackageWebhookConsumer.php` |

## Request Lifecycle

```mermaid
flowchart TD
    A[Browser or webhook] --> B[Caddy / FrankenPHP]
    B --> C[Symfony kernel]
    C --> D[Controller]
    D --> E[Service layer]
    E --> F[Persister or process]
    F --> G[Satis config or build output]
```

## Main Flows

### Admin UI Flow

```mermaid
sequenceDiagram
    participant U as User
    participant C as Controller
    participant M as RepositoryManager
    participant P as JsonPersister

    U->>C: Open /admin or edit form
    C->>M: Read current config
    M->>P: Load JSON config file
    P-->>M: Configuration DTO
    M-->>C: Repositories / config
    C-->>U: Twig response
```

### Build Flow

```mermaid
sequenceDiagram
    participant U as User
    participant C as SatisController
    participant S as SatisManager
    participant P as ProcessFactory
    participant X as Composer Satis

    U->>C: Click build
    C->>S: run()
    S->>P: Create command
    P->>X: Start satis build process
    X-->>S: stdout / exit code
    S-->>C: streamed output
    C-->>U: process response page
```

### Webhook Flow

```mermaid
flowchart TD
    A[Webhook POST /webhook/package] --> B[PackageRequestParser]
    B --> C{X-Authentication-Token valid?}
    C -- no --> D[401 reject]
    C -- yes --> E{repository payload present?}
    E -- no --> F[400 reject]
    E -- yes --> G[RemoteEvent package]
    G --> H[PackageWebhookConsumer]
    H --> I[BuildEvent]
    I --> J[SatisManager listener]
    J --> K[Build repository]
```

## Why This Shape

- File storage keeps setup simple and portable.
- Symfony services keep behavior testable.
- Event-driven build flow decouples UI actions from build execution.
- Locking prevents concurrent config writes and concurrent builds.

## Configuration Flow

1. Environment vars load from `.env` or Docker.
2. Symfony parameters resolve `SATIS_CONFIG`, `SATIS_LOG`, `COMPOSER_HOME`, and `COMPOSER_CACHE`.
3. `RepositoryManager` loads JSON config through `JsonPersister`.
4. `Configuration` DTO holds repository state.
5. `JsonPersister` serializes back to Satis JSON format.

## Build Input And Output

- Input config file: `SATIS_CONFIG`
- Output directory: `Configuration::outputDir`
- Build command: `composer/satis build`
- Optional per-repository build: build listener passes repository name to Satis

## Related Docs

- [Developer guide](../development/guide.md)
- [Docker guide](../docker/overview.md)
- [Testing guide](../testing/guide.md)
- [Webhook API](../api/webhook.md)
