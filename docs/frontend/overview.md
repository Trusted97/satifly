# Frontend Overview

Satifly frontend is intentionally small.

## Stack

- Twig templates
- Bootstrap 5 assets vendored in `public/`
- A small vanilla JS collection handler

## Structure

| Area          | Code                              |
|---------------|-----------------------------------|
| Base layout   | `templates/base.html.twig`        |
| Pages         | `templates/views/*`               |
| Form partials | `templates/views/form/*`          |
| Collection JS | `public/js/collection-handler.js` |

## Design Rules

- Keep UI componentized in Twig partials.
- Use shared layout for shell, alerts, and spacing.
- Prefer server-rendered forms for stateful flows.
- Keep JS minimal and local to form behavior.

## Current UI Notes

- No Stimulus controllers today
- No AssetMapper today
- No frontend build step today
- Bootstrap CSS and JS are checked in

## Extending UI

- Add new page under `templates/views/`
- Reuse `base.html.twig`
- Put repeated form markup in partials
- Keep JS logic in `public/js/` if needed

## Related Docs

- [Architecture overview](../architecture/overview.md)
- [Development guide](../development/guide.md)
