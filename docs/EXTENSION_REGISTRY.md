# Gaming Hub Extension Registry v0.6.0

Gaming Hub extensions are ordinary Azuriom plugins. Core provides discovery, validation, staged installation, metadata, and rollback; Azuriom remains responsible for plugin loading and lifecycle.

## Sources and trust

Official, custom registry, and direct public GitHub repository sources are supported. Custom sources require explicit acknowledgement because extensions execute PHP with the website process privileges. GitHub branches, commits, source archives, private repositories, tokens, automatic updates, Core self-update, themes, and destructive data purge are unsupported. Validated file-only uninstall retains database data.

## Security

HTTPS is required. Localhost, loopback, link-local, private and reserved IPs are rejected unless the administrator enables the documented private-host setting. Redirects are rejected unless a future implementation resolves and revalidates every hop. Archives are staged, size/count limited, single-root, symlink-free and path-traversal-free. SHA-256 is the only accepted checksum.

## Filesystem

The PHP user needs write access only to `storage/app/gaming-hub/extensions/{staging,backups,logs}` and Azuriom's `plugins` directory. Do not chmod the whole application to 777. For Docker, align the container PHP UID/GID with mounted-volume ownership and grant group write access to those paths.

## Lifecycle limitation

Core uses Azuriom's PluginManager for enabled-state detection, disable, enable, migration, and cache refresh, with fixed Artisan lifecycle commands as a compatibility fallback. No arbitrary shell command is executed.

## Retention

Operation metadata is kept in the database. Administrators should prune successful logs older than the configured 180-day retention in normal maintenance; v0.6.0 does not add a scheduler.
