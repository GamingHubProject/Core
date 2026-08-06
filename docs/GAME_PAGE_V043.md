# Game Detail rendering contract — v0.4.3

## Server data flow

`GameController::show()` retrieves enabled and public Servers directly from the resolved Game, orders them by `position`, `name`, and `id`, and maps every record to `PublicServerData`.

The collection is passed to Blade as `publicGameServers`. This deliberately avoids the generic `servers` variable name because Azuriom installations and themes may share or compose a global `servers` value.

Provider resolution changes only the status fields. A missing, invalid, or unavailable provider produces `status = unknown`; it never removes a Server from the collection.

## Show Servers setting

The setting defaults to `true`. Stored booleans are normalized from native booleans, `0`/`1`, and common string representations including `true`, `false`, `yes`, `no`, `on`, and `off`.

When disabled, the entire Servers section is omitted. When enabled, the exact public Server collection is rendered.

## Layout

Azuriom's application layout owns the outer `.content` wrapper. The plugin starts with `.gh-game-page` only. Overview and Description cards use natural height; only Server cards use equal-height flex behavior.
