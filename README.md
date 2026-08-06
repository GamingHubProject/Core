# Gaming Hub Core v0.7.0

Gaming Hub Core is the platform plugin for Azuriom Gaming Hub installations. It provides:

- Game and Server registries;
- generic extension-safe Provider registration and CRUD;
- deterministic Provider priority;
- normalized Shared Data Gateway reads;
- public Game Directory and Server pages;
- public-data and directory settings;
- native Azuriom navbar targets and theme-compatible semantic markup.

Hierarchy: `Game -> many Servers -> many Provider Instances`.

The bundled provider remains Manual Status. External integrations such as Pelican and Pterodactyl remain separate Provider extensions.

## Package lifecycle ownership

Starting with v0.7.0, Gaming Hub Core no longer installs, updates, uninstalls, discovers, or manages packages. Gaming Hub Manager is the standalone package lifecycle owner.

Core does not require Gaming Hub Manager. When Manager is enabled, Core adds one permission-aware **Package Manager** link that opens `gaming-hub-manager.admin.overview`. When Manager is absent or disabled, Core registers no link and continues normally.

Legacy Core package metadata tables and migrations remain intact for non-destructive compatibility and Gaming Hub Manager import. See `docs/LEGACY_PACKAGE_METADATA.md`.

## Documentation

See `docs/SERVERS.md`, `docs/PROVIDERS.md`, `docs/SHARED_DATA_GATEWAY.md`, `docs/GAME_DIRECTORY.md`, `docs/NAVBAR.md`, and `INSTALL.md`.
