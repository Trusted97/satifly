# Testing Guide

Satifly uses PHPUnit only. No browser test stack beyond Symfony WebTestCase.

## Test Types

| Path                | Purpose                            |
|---------------------|------------------------------------|
| `tests/Application` | App boot and page-level checks     |
| `tests/Controller`  | HTTP behavior                      |
| `tests/DTO`         | Value objects and config models    |
| `tests/Form`        | Form wiring and validation         |
| `tests/Integration` | End-to-end build flow              |
| `tests/Manager`     | Repository/config manager behavior |
| `tests/Persister`   | File and JSON persistence          |
| `tests/Process`     | Process env and factory behavior   |
| `tests/Service`     | Service logic                      |

## Run Tests

```bash
make test
```

Or:

```bash
vendor/bin/phpunit -c phpunit.xml.dist --no-coverage
```

## Fixtures

- JSON fixtures live in `tests/fixtures`
- Composer lock schema tests use `src/Resources/schemas/composer_lock.json`

## Temp Files

Tests use real temp directories now.

- See `tests/Traits/TempFilesystemTrait.php`
- No virtual filesystem dependency

## Writing New Tests

- Prefer one behavior per test.
- Use real temp dirs for file I/O.
- Keep fixtures minimal.
- Assert output file paths, not implementation details.

## Common Patterns

```php
$client = self::createClient();
$client->request('GET', '/admin');
self::assertResponseIsSuccessful();
```

```php
$this->writeTempFile('satis.json', json_encode($config));
```

## Related Docs

- [Development guide](../development/guide.md)
- [Architecture overview](../architecture/overview.md)
- [Troubleshooting guide](../troubleshooting/guide.md)
