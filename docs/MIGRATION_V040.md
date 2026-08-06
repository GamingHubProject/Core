# v0.4.0 migration

The migration creates `gaminghub_servers` and adds `server_id` to provider instances. For each game with existing v0.3.x providers, it creates one enabled/public `Default Server` and attaches every provider from that game to it in the same order. No provider configuration is rewritten or discarded.

The legacy `game_id` column remains as deprecated rollback metadata. Runtime reads and writes use `server_id`; new provider records also retain `game_id` for downgrade safety. Do not manually clear `server_id`.

No manual command is required. After upgrade, administrators may rename the migrated server, edit its slug, or create additional servers and recreate/move provider assignments as desired.
