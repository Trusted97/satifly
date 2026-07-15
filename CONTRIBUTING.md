# Contributing to Satifly

Thanks for helping improve Satifly.

## Before You Start

Read:

- [Documentation hub](docs/README.md)
- [Architecture overview](docs/architecture/overview.md)
- [Developer guide](docs/development/guide.md)
- [Testing guide](docs/testing/guide.md)

## Branch Naming

- Use `codex/...` for local agent work
- Use short, task-focused names
- Example: `codex/docs-refresh`

## Commit Style

Use conventional commits.

Examples:

```text
docs: refresh architecture guide
fix: handle empty webhook payload
refactor: simplify repository manager
test: replace vfs stream with temp files
```

## Code Style

- PHP: PSR-12 plus project fixer config
- Twig: keep templates small, reusable, and readable
- Comments: explain why, not obvious what

Run before PR:

```bash
make style
make test
```

## Pull Requests

1. Keep changes small.
2. Update docs when behavior changes.
3. Include tests for behavior changes.
4. Describe user impact clearly.

## Review Expectations

- Passing tests
- Clear diff
- No stale docs
- No accidental behavior changes

## Issue Reports

Include:

- what you expected
- what happened
- repro steps
- environment details
- logs or screenshots when useful

## Release Workflow

Current flow is simple and manual:

1. Merge reviewed changes.
2. Run full test suite.
3. Verify docs and changelog notes.
4. Tag release if needed.

## Security Reports

Do not open public issue for security bug. Contact maintainers directly.
