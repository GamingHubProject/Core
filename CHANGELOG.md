# Changelog

## 0.7.0

- Converted Gaming Hub Core into a pure platform plugin.
- Removed Core-owned package lifecycle routes, controllers, services, models, requests, views, configuration, permissions, tests, examples, and lifecycle documentation.
- Removed Core's ZIP and semantic-version package-management dependency declarations.
- Retained legacy package metadata migrations and database structures without destructive changes for Gaming Hub Manager import and downgrade safety.
- Added one optional permission-aware **Package Manager** navigation link when `gaming-hub-manager` is enabled.
- Preserved Games, Servers, Providers, public pages, navbar targets, theme compatibility, Provider runtime, and Shared Data Gateway behavior.
- No database schema changes.

## 0.6.6

- Fixed extension-owned FormRequests dropping registered provider configuration fields before Core validation.
- Reconciles only Provider Registry-declared fields from the raw request; arbitrary extension fields and credentials remain excluded.
- Prefixes configuration validation errors with `configuration.` so provider forms render them at the correct field.
- Added sanitized provider creation tracing for request, validation, lifecycle entry, repository save, transaction commit, and failure stages.
- Preserves old input and exposes validation/lifecycle errors through Azuriom admin alerts instead of silent redirects.
- No database schema changes.

## 0.6.5

- Fix extension-owned provider forms bypassing Core ordering by enforcing append priority and immutable ownership/priority through the generic `ProviderInstance` model observer.
- Accept and discard transient configuration keys not declared by the selected Provider Registry type, while preserving validation for all declared fields.
- Convert provider priority to a one-based contiguous sequence and add a unique `(server_id, position)` database constraint.
- Use collision-safe two-phase ordering writes inside locked transactions for create, update, move, delete, and normalization.
- Keep ordinary extension-owned edits from changing Server ownership or priority; priority changes continue through Core move actions.
- Add mixed Manual/Pelican/Pterodactyl-style CRUD simulations, duplicate creation, recreate-after-delete, registry compatibility, capability priority, transaction, and persistence checks.

## 0.6.4

- Fix provider route-model binding for toggle, move, and delete actions by matching the `{provider}` route parameter.
- Add a generic server-scoped provider lifecycle service with transactions, Server/provider row locking, ownership validation, and safe validation errors.
- Normalize provider positions to a contiguous zero-based sequence after create, update, move, delete, and legacy migration.
- Add deterministic `position, id` capability priority and preserve it across reloads and container restarts.
- Add generic provider deletion events without depending on extension classes or deleting Gaming Hub Panel global connections.
- Replace no-op provider cache invalidation with per-provider cache generations.
- Correct provider action forms, boundary disabling, success flashes, and validation-error rendering.
- Add focused provider lifecycle, ordering, capability-priority, cache, route, and UI contract tests.

## 0.6.3

- Add a dedicated extension update lifecycle instead of routing updates through fresh installation.
- Validate installed and staged manifest IDs, enforce newer-version updates, and block downgrades.
- Preserve enabled/disabled state across update and restore files, metadata, and state on rollback.
- Add timestamped update backups with configurable successful-backup retention.
- Add guarded file-only uninstall with explicit confirmation, dependency blocking, Core self-protection, operation logs, and retained database data.
- Reconcile filesystem-installed Gaming Hub extensions into metadata and show Update, Up to date, Installed, Incompatible, and Uninstall states in administration.
- Correct Azuriom lifecycle integration to use PluginManager and the `id` command argument.
- Expand lifecycle stages, safe error reporting, and focused update/uninstall/security tests.

## 0.6.2

- Fix mismatched inline Blade directive compilation in the Extensions administration page.
- Rewrite Extension Installer administration views with explicit, correctly nested directives.
- Preserve Installed, Available, Registries, Install Logs, lifecycle, installer, and security behavior.

## 0.6.1

- Validate and follow GitHub-owned HTTPS release download redirects.
- Convert installer exceptions into actionable administration validation messages.
- Add queued/resolving/downloading/validating/extracting/installing/completed/failed operation stages.
- Persist structured lifecycle events and close interrupted legacy operations.
- Harden cleanup and update rollback behavior.
- Populate Installed Extensions immediately after a successful transaction.


## 0.6.0
- Added secure admin-only extension sources, normalized registry/package schemas, GitHub Releases discovery, SSRF protections, ZIP/checksum validation, staged transactional installation, update backups/rollback, metadata and audit logs.
- Core self-update, themes, uninstall, private GitHub repositories, branch/commit installs, arbitrary shell commands, and automatic updates remain unsupported.


## 0.5.0
- Added normalized Shared Data Gateway and capability reader registry.
- Added immutable shared-data, status, metrics and public-policy DTOs.
- Migrated Manual Provider public reads to a registered server-status reader.
- Added global and per-Server public statistic and attribution policies.
- Added versioned optional read caching and safe normalized provider errors.
- Updated public Server presentation to consume policy-filtered gateway data.

## 0.4.4

- Fix the public Server Detail controller/view contract with collision-resistant DTO variable names.
- Add nested Game/Server validation and a private versioned runtime view.
- Make provider absence resolve to Unknown without removing or crashing the Server page.
- Remove unconditional public capability placeholders and guard all optional metadata.


## 0.4.1
- Replaced the legacy public Game page with the Server Registry-driven canonical renderer.
- Added Game Page presentation settings for server cards, status, messages, join/address output, and navigation.
- Added the scoped Game Navigation extension point.
- Removed public provider and future-capability panels from the Game page.
- Preserved Gaming Hub Theme v1.2.0 compatibility through semantic Core-owned markup.


## 0.4.0

- Added the Server Registry and nested administration CRUD.
- Migrated provider ownership from games to servers without losing assignments.
- Added server public pages, game server cards and native server navbar targets.
- Changed capability resolution to server scope.
- Added the Show Servers on Game page setting and server/provider documentation.


## 0.3.3

- Fixed the complete Game Directory settings-to-rendering contract.
- Added deterministic grid, density, container and fallback classes/data attributes.
- Made Core own the canonical `/games` renderer so outdated theme overrides cannot discard settings.
- Added guarded settings-cache invalidation after save.
- Added rendered-output and theme-override compatibility contract tests.

## 0.3.2

- Added configurable public Games directory presentation.
- Added dynamic native navbar targets for enabled games.

## 0.4.2
- Replaced the ambiguous public Game Detail view path with the private `gaming-hub-core-runtime` namespace.
- Added a versioned Core-owned Game Detail renderer that cannot be replaced by theme namespace overrides.
- Restored banner-backed game hero presentation with a polished no-banner fallback.
- Preserved server-registry cards, game navigation, settings, and server-scoped capability resolution.
- Added runtime markup markers for deployment verification.

## 0.4.3

- Fixed the public Game Detail Server collection contract by using the collision-resistant `publicGameServers` view variable.
- Separated public Server retrieval from optional provider/status presentation.
- Ensured Servers without providers always render with `Unknown` status.
- Normalized boolean Game Page settings across boolean, integer, and string storage representations.
- Removed the nested Azuriom `.content` wrapper from the Game Detail page.
- Restored natural height for Overview and Description cards while retaining equal-height Server cards.
- Versioned the private runtime Game Detail view namespace to v0.4.3.
