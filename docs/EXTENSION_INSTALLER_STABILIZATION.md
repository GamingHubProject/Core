# Extension Lifecycle Stabilization (v0.6.3)

## Download and package validation

Packaged GitHub Release assets retain the existing HTTPS-only download, validated GitHub redirect-chain, checksum, archive traversal, symlink, file-count, extracted-size, manifest, and compatibility checks. The currently installed extension is not modified until the candidate package has passed validation and same-filesystem staging is complete.

## Operation stages

Install and update operations use explicit lifecycle stages. Updates progress through:

`resolving → downloading → validating → staging → backing_up → disabling → replacing → migrating → enabling → cleaning → completed`

Failures before replacement end as `failed`. Failures after state or file changes transition through `rolling_back` and end as `rolled_back` or `rollback_failed`, with `result=failed` and a recorded original failed stage.

Uninstall operations use:

`resolving → disabling → removing → cleaning → completed`

Every controller failure path closes the operation in a terminal state. Stale legacy `running` records are still marked interrupted by the administration page.

## Dedicated update lifecycle

Update resolves installed metadata and the live manifests before downloading a release. The candidate manifest ID and directory must match the installed extension. Same-version releases report **Up to date** and downgrades remain blocked. A timestamped copy of the current extension is created before disable/replacement. The previous enabled or disabled state is restored after migration.

If replacement, migration, metadata persistence, cache refresh, or re-enable fails, Core restores the previous directory, installed-extension metadata, and enabled state where possible.

## Safe file uninstall

Uninstall requires an explicit confirmation page, disables the extension first, blocks installed dependents, protects Gaming Hub Core from self-removal, and restricts the source directory to `plugins/{validated-extension-id}`. Files are moved into guarded uninstall staging before metadata is removed. Database tables and user data are retained; v0.6.3 does not run destructive down migrations or purge hooks.

## Graceful failures

Recoverable lifecycle failures return to the Extensions logs page with the operation type, failed stage, a sanitized message, and rollback status. Application/storage paths, URL query credentials, stack traces, and secrets are not rendered.

## Deployment

After upgrading, run the normal plugin migrations and clear application/view caches. No existing source, installed-extension, or operation table is removed. Successful update backups are retained by default and can be disabled with `GAMING_HUB_EXTENSIONS_RETAIN_UPDATE_BACKUPS=false`.
