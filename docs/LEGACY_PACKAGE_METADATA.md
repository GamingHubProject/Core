# Legacy package metadata

Gaming Hub Core versions 0.6.0 through 0.6.6 contained an extension package lifecycle subsystem. Gaming Hub Manager is now the sole package lifecycle owner.

Core v0.7.0 no longer registers package routes, administration pages, permissions, models, services, registry clients, release resolvers, installers, updaters, uninstallers, or operation-log UI.

For non-destructive compatibility, the historical migrations and resulting tables remain:

- `gaminghub_extension_sources`;
- `gaminghub_installed_extensions`;
- `gaminghub_extension_operations`.

Core does not read from or write to these tables in v0.7.0. Gaming Hub Manager may import them through its own legacy importer. Removing the tables is intentionally outside this release.
