# Provider Registry

## Concepts

A **provider type** is registered by Core or an extension during plugin boot. It declares an ID, owner, capabilities, availability, and configuration fields. A **provider instance** is a generic database row assigned to exactly one Gaming Hub Server. Its JSON configuration belongs to that mapping and is removed with the mapping.

Supported capabilities are: `server-status`, `players`, `player-identities`, `player-positions`, `chat`, `commands`, `configuration`, and `metrics`.

## Registering a type

Resolve `ProviderTypeRegistry` and register a detached `ProviderType` during plugin boot. Duplicate IDs, malformed IDs, unsupported capabilities, malformed owner metadata, duplicate field keys, and unsupported field types are rejected.

```php
$registry->register(new ProviderType(
    id: 'example',
    name: 'Example',
    description: 'Example provider.',
    pluginId: 'example-plugin',
    pluginName: 'Example Plugin',
    capabilities: ['server-status'],
    fields: [
        new ProviderConfigurationField(
            'status',
            'Status',
            'select',
            true,
            ['online', 'offline'],
        ),
    ],
));
```

Provider lifecycle operations use `ProviderInstance` and `ProviderTypeRegistry`; they do not hard-code Manual, Pelican, Pterodactyl, or another extension type.

## Configuration validation

Only fields declared by the selected provider type are persisted. Declared values are validated before persistence. Transient fields added by a shared extension form are discarded when they do not belong to the selected registry type, so one extension form cannot break another registered provider type. The built-in `manual` type accepts `status` (`online`, `offline`, `maintenance`, `unknown`) and an optional 500-character `display_message`.

Deleting a provider instance deletes only that Server mapping and its JSON configuration. It does not delete the Server, another provider, or an external/global connection referenced by the configuration.

Extensions may listen for `ProviderDeleting` and `ProviderDeleted` when they own additional mapping-specific data. Core does not call extension implementation classes directly.

## Ordering and capability priority

Provider `position` is a one-based, contiguous priority within one Server:

```text
1 = highest priority
2 = next priority
3 = next priority
```

Core create, move, and delete operations run in database transactions, lock the selected Server and its provider rows, and normalize positions. Ordering writes use temporary negative values before final positive priorities so the unique `(server_id, position)` constraint is never violated during a swap. Legacy duplicate, zero, gapped, and arbitrary positions are normalized by the v0.6.5 migration using deterministic `position, id` ordering.

Capability resolution follows:

```text
Server
→ enabled providers ordered by position, then ID
→ registered and available provider types supporting the capability
→ first eligible provider
```

An unsupported provider type is skipped for that capability, and disabled provider instances are ignored. Reordering one Server never reorders providers assigned to another Server.

## Extension-owned provider forms

Extensions may provide specialized provider configuration views and controllers while continuing to persist the generic `ProviderInstance` model. Core observes that model to preserve the shared lifecycle invariants:

- a newly created mapping is appended to its Server with the next priority;
- `game_id` is derived from the selected Server;
- ordinary extension-owned updates cannot move a mapping to another Game/Server or overwrite its priority;
- direct model deletion normalizes the remaining mappings;
- Core lifecycle operations suppress observer re-entry and remain the authoritative move/delete path.

Extension create/delete operations should remain inside database transactions. Existing Gaming Hub Panel create/update transactions are compatible without changes. Provider-specific global connections remain extension-owned and are not deleted by Core.

## Querying instances

Inject `ProviderInstances` and use:

- `forServer($serverId)`
- `findForServer($serverId, $providerId)`
- `validatedConfiguration($serverId, $providerId)`
- `enabledForServerByCapability($serverId, $capability)`

Detached DTOs, rather than Eloquent models, are the public return type.

## Cache invalidation

Shared Data Gateway cache keys include a per-provider generation. Saving, toggling, reordering, or deleting a provider advances that generation so stale data cannot remain selected after lifecycle changes.

## Secrets

Normal administration tables never render provider configuration and the model hides it from array/JSON serialization. Provider extensions that store credentials remain responsible for using their documented secure storage strategy.

## Provider creation validation compatibility (v0.6.6)

Extension-owned provider forms may validate extension mapping fields before delegating generic configuration validation to Core. Laravel removes fields that are absent from an extension FormRequest's rules when `validated()` is called. Core therefore reconciles only fields declared by the selected Provider Registry type from the raw HTTP `configuration` payload before applying the registered type rules. Validated values take precedence, arbitrary keys are discarded, and credentials are never copied unless the Provider Registry explicitly declares them.

Creation validation failures use `configuration.<field>` error keys and are also flashed to Azuriom's global admin error alert. Sanitized trace entries begin with `Gaming Hub provider creation:` and cover request receipt, validated payload, lifecycle/DTO entry, repository save, transaction commit, and failure. Set `GAMING_HUB_PROVIDER_TRACE=false` after runtime verification.
