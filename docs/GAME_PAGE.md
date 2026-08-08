# Public Game Page

Gaming Hub Core owns the canonical public Game page renderer. It still extends the active theme's `layouts.app` layout and uses Bootstrap plus `gh-*` semantic hooks, but a stale theme override cannot replace the Server Registry markup.

## Data flow

`GameController::show()` loads one enabled game, then its enabled and public servers ordered by `position`, `name`, and `id`. Each server is presented independently by `PublicServerPresenter`, which asks the server-scoped `CapabilityResolver` for `server-status` and reads only validated public Manual Provider fields (`status` and `display_message`). Provider type names and configuration are never passed to the Game page.

## Settings

Keys use `gaming-hub-core.game-page.*`:

- `show_servers` (default true)
- `server_density`: `compact` or `standard` (default compact)
- `server_columns`: 1, 2, or 3 (default 2)
- `show_server_descriptions` (default true)
- `show_status` (default true)
- `show_provider_message` (default true)
- `show_join_button` (default true)
- `show_address`: `hidden`, `hostname`, or `hostname_and_port` (default hidden)
- `show_navigation` (default true)

Join links render only when `join_url` passes URL validation. Address output never reads provider configuration.

## Theme contract

Core emits `gh-game-page`, `gh-game-hero`, `gh-game-section`, `gh-server-grid--1..3`, `gh-server-card--compact|standard`, `gh-status-*`, `gh-game-navigation`, and `gh-empty-state`. Minimal fallback CSS provides responsive behavior. Themes may style these hooks without duplicating settings logic.
