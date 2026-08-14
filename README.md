# Gaming Hub Core

Domain models, capability decisions, and data normalization for the Gaming
Hub platform. Ships as a separate, independently-versioned Composer package
that Platform requires — kept separate so it can evolve fast without
risking the Platform monolith.

## What lives here

- **Domain models:** `Game`, `Server`, `Instance`, `Provider` (Eloquent, with
  migrations and factories)
- Capability decisions and data normalization land here in a later step

## What does NOT live here

Per the platform's architecture, Core:
- Never composes UI, applies themes, or manages assets — those are Platform
  concerns (Experience System, Theme System, Asset Management)
- Never speaks to connectors directly — that's Panel's job; Core only
  decides *which* connector should serve a capability and normalizes the
  data Panel hands back
- Doesn't know about `ServerGroup`, `Map`, `ConfigurationPreset`,
  `GameExtension`, `Page`, or `Theme` — those are Platform-side and query
  Core's models by foreign key directly rather than through a relation
  defined here

## Using it

Platform requires this as a local path package during development:

```json
"repositories": [
    { "type": "path", "url": "../Core", "options": { "symlink": false } }
],
"require": {
    "gaminghubproject/core": "*"
}
```

`CoreServiceProvider` registers Core's migrations with the host app — no
further wiring needed beyond adding it to `bootstrap/providers.php`.

## Versioning

Independent of Platform. `v{release}.{milestone}.{small-milestone}{hotfix}`,
same as every other Gaming Hub component.
