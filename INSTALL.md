# Installation and upgrade

## Install

1. Copy the `gaming-hub-core` directory into Azuriom's `plugins` directory.
2. Enable Gaming Hub Core through Azuriom administration.
3. Run the normal plugin migration flow, or execute `php artisan migrate --force` when required by the deployment.
4. Run `php artisan optimize:clear`.
5. Grant the Games, Servers, Providers, and Settings permissions to the appropriate administrator roles.

Gaming Hub Manager is optional. Core works without it.

## Upgrade from v0.6.6 to v0.7.0

1. Back up the database and `plugins/gaming-hub-core`.
2. Ensure Gaming Hub Manager has imported any legacy package metadata needed by the installation.
3. Replace only the `gaming-hub-core` plugin directory with v0.7.0.
4. Run `php artisan optimize:clear`.
5. A new migration is not introduced by v0.7.0. Existing Core migrations remain available and must not be deleted from an upgraded installation.
6. Verify Games, Servers, Providers, public pages, and Gaming Hub Panel Provider types.
7. Verify that no Gaming Hub Core `/extensions` routes appear in `php artisan route:list`.
8. When Gaming Hub Manager is enabled, verify **Gaming Hub → Package Manager** opens the Manager overview.
9. Disable Gaming Hub Manager temporarily and confirm the Package Manager link disappears while Core platform pages continue to work.

## Legacy package metadata

v0.7.0 does not drop or mutate:

- `gaminghub_extension_sources`;
- `gaminghub_installed_extensions`;
- `gaminghub_extension_operations`.

These tables are deprecated Core-owned metadata retained for Gaming Hub Manager import and downgrade safety. Core no longer reads from or writes to them.

No Gaming Hub Panel change is required.
