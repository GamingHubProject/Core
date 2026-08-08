# Public data policies

Policy resolution order:

1. Per-Server override
2. Global Azuriom setting
3. Safe built-in default

Each statistic has independent visibility and source-attribution policy. Infrastructure metrics, version, uptime and timestamps are hidden by default. Server state, provider message and player counts are visible by default. Attribution is always hidden by default.

Per-Server values are `inherit`, `show`, or `hide`. Attribution exposes only the reader's safe source label. Provider IDs, instance IDs, credentials, URLs, configuration and diagnostics are never included in public-safe results.
