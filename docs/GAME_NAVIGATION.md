# Game Navigation Extension Point

Future plugins may resolve `Azuriom\Plugin\GamingHubCore\Navigation\GameNavigation` (also aliased as `gaminghub.game-navigation`) from Laravel's service container and register a `GameNavigationItem` during their service provider boot method.

Each item has a unique ID, label, URL resolver scoped to the current Game, optional icon identifier, order, optional visibility callback, and optional active-state callback. Duplicate IDs throw `DuplicateGameNavigationItem`. Contributions are sorted by order, label, and ID. Empty, unavailable, or failing optional contributions are hidden rather than breaking the Game page.

Core registers only `Overview` and `Servers`. It does not create Guide, Wiki, Map, Trading, or Guild pages.
