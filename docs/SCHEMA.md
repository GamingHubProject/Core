# Database schema

Existing table: `gaminghub_games`.

New table: `gaminghub_provider_instances`
- `id` unsigned integer primary key
- `game_id` foreign key to `gaminghub_games.id`, cascade delete
- `provider_type` string(100), indexed
- `name` string
- `enabled` boolean, indexed
- `position` integer, indexed
- `configuration` JSON
- timestamps

The composite game/enabled/position index supports ordered capability queries. Configuration is never rendered as raw JSON in normal admin list views.
