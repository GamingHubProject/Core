# Public Game Detail view ownership (v0.4.2)

Gaming Hub Core owns the public Game Detail structure and data contract. The controller renders
`gaming-hub-core-runtime-v042::games.show-v042`, a private namespace registered directly against the
plugin's `resources/views/runtime` directory.

Gaming Hub Theme must not copy or replace this view. It styles the semantic `gh-*` classes emitted
by Core. This prevents Server Registry additions from being suppressed by stale full-view overrides.

After deployment, the page root contains `data-gh-core-game-view="0.4.2"`. This is a diagnostic
contract confirming that the request reached the current renderer.

Deployment cache commands:

```bash
php artisan optimize:clear
php artisan view:clear
```
