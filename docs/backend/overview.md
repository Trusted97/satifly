# Backend Overview

Satifly backend is Symfony services plus file-based persistence.

## Main Subsystems

| Subsystem   | Code                                 |
|-------------|--------------------------------------|
| Controllers | `src/Controller/*`                   |
| Forms       | `src/Form/*`                         |
| DTOs        | `src/DTO/*`                          |
| Services    | `src/Service/*`                      |
| Persisters  | `src/Persister/*`                    |
| Validation  | `src/Validator/*`                    |
| Webhooks    | `src/Webhook/*`, `src/RemoteEvent/*` |
| Commands    | `src/Command/*`                      |
| Events      | `src/Event/*`                        |

## Controller Flow

- `IndexController` serves public landing page or fallback.
- `SecurityController` handles login and logout.
- `AdminController` handles repository CRUD and lock import.
- `SatisController` handles build page, build stream, and config editor.

## Services

- `RepositoryManager` loads and mutates current config.
- `JsonPersister` serializes DTOs to Satis JSON.
- `FilePersister` reads and writes files on disk.
- `LockProcessor` imports repositories from `composer.lock`.
- `SatisManager` builds Satis output and listens for `BuildEvent`.
- `ProcessFactory` creates Symfony Process instances.

## Event Flow

- `BuildEvent` triggers `SatisManager::onBuild()`
- Webhooks dispatch `BuildEvent` through `PackageWebhookConsumer`

## Configuration Flow

1. Symfony resolves parameters from env vars.
2. `RepositoryManager` loads config.
3. DTOs model current registry state.
4. Persister writes back to JSON.

## Related Docs

- [Architecture overview](../architecture/overview.md)
- [Development guide](../development/guide.md)
- [Testing guide](../testing/guide.md)
