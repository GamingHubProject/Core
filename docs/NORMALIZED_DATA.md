# Normalized data

Generic keys:

- `server.state`
- `server.version`
- `server.name`
- `server.message`
- `players.current`
- `players.maximum`
- `resources.cpu_percent`
- `resources.memory_used_bytes`
- `resources.memory_limit_bytes`
- `resources.disk_used_bytes`
- `uptime.seconds`
- `observed_at`
- `source_updated_at`

Unknown unnamespaced keys are rejected. Future game-specific keys must be namespaced, such as `palworld.world_name`.

`ServerStatusData`, `MetricsData` and `SharedDataResult` are immutable detached value objects and contain no Eloquent models. Dates serialize as ISO-8601 strings.
