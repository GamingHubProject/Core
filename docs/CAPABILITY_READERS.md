# Capability reader registration

Integration plugins register one stateless reader for a provider type and capability:

```php
$registry->register('pelican', 'server-status', PelicanServerStatusReader::class);
```

The provider type and capability must already exist in the Provider Registry. Duplicate registrations are rejected. Reader classes implement `CapabilityReader`, are resolved by Laravel's container, and return `SharedDataResult` containing normalized keys only.

Core capabilities are: `server-status`, `players`, `player-identities`, `player-positions`, `chat`, `commands`, `configuration`, and `metrics`.

Future provider plugins translate external responses into Core DTOs. Core does not reference Pelican, Pterodactyl, Palworld, Minecraft or RCON classes.
