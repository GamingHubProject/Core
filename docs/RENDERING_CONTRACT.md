# Games directory rendering contract

Gaming Hub Core owns the `/games` markup. Themes must style, not replace, the following semantic state classes:

| Setting | Stored values | Rendered contract | Effect |
|---|---|---|---|
| Columns | `1`, `2`, `3`, `4` | `.gh-games-grid--{value}` and `data-gh-columns` | One column below 768px; up to two on tablets; the selected count from 1200px. |
| Density | `compact`, `standard` | `.gh-game-card--compact` / `.gh-game-card--standard` and `data-gh-density` | Changes media ratio, padding, spacing, icon size and minimum height. |
| Container width | `normal`, `wide`, `full` | `.gh-page-container`, `.gh-page-container-wide`, `.gh-page-container-full` and `data-gh-container` | 1140px, 1600px or available viewport width with safe padding. |
| No-banner fallback | `compact`, `media` | `.gh-games-fallback--compact` / `.gh-games-fallback--media` and `data-gh-fallback` | Compact omits media for null/empty banner; media renders bundled placeholder. |

A real non-empty banner always renders the media region. Theme CSS may change visual tokens, but must not force fixed grid columns, wrapper widths, media visibility or card density independently.

Core renders this view by absolute plugin path. This intentionally prevents an outdated theme Blade override from discarding the settings. Existing game-detail theme overrides remain supported.
