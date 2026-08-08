# Upgrade to v0.4.1

No database migration is required. Existing v0.4.0 Servers and provider assignments remain unchanged. Providers migrated from pre-v0.4 installations stay assigned to the generated Default Server and are resolved only through that Server.

The old `gaming-hub-core.directory.show_servers_on_game_page` value remains stored for compatibility, but the v0.4.1 Game Page uses the new `gaming-hub-core.game-page.show_servers` setting.

After replacing the plugin, open Directory Settings, review the new Game Page Settings section, and save once. Azuriom's settings cache and common compatibility cache keys are invalidated automatically.
