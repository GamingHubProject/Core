# Upgrade to v0.5.0

The migration creates `gaminghub_public_data_policies` for per-Server tri-state overrides. Global defaults use Azuriom's normal settings storage. Existing Games, Servers, provider instances, Manual configurations, routes and public pages remain unchanged.

The Manual Provider now registers a `server-status` reader but retains its existing `status` and `display_message` configuration keys. Administrators do not recreate providers.
