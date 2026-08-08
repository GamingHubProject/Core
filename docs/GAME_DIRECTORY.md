# Public Game Directory

Gaming Hub Core exposes `/games` and the existing `/games/{slug}` detail pages.

## Settings

Authorized administrators can open **Gaming Hub → Directory Settings**. Settings use Azuriom's shared `settings` storage with the `gaming-hub-core.directory.*` prefix.

Defaults: title `Games`, empty description, 3 columns, compact density, normal container, all content toggles enabled, enabled-game count enabled, and compact no-banner fallback.

The title is limited to 120 characters and the description to 2,000 characters. The description is intentionally plain text: HTML is escaped and line breaks are preserved.

## Layout

The directory uses the active theme's `layouts.app`, Bootstrap containers, rows, cards, spacing, and buttons. `normal`, `wide`, and `full` select `container`, `container-xxl`, or a padded `container-fluid`. Even full mode retains horizontal padding.

Columns are responsive. A configured 4-column grid becomes one column on narrow phones, two on small screens, and four on wide screens. Cards are flex-aligned per row and cannot create horizontal overflow.

## No-banner fallback

`compact` omits the media area entirely. `media` renders the bundled 16:7 SVG placeholder. Real banners preserve the same aspect ratio and use `object-fit: cover`.
