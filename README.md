# Views Vanilla JavaScript Lightbox (VVJL)

A vanilla-JavaScript lightbox **Views format** for Drupal 11.3+ / 12. Renders
Views rows as a clickable image grid; clicking an image opens a modal with
previous/next navigation and overlay color/opacity controls.

v2.0 builds on the **`drupal/vvj_core` foundation** — composer auto-installs it
on `composer update drupal/vvjl`. The lightbox behavior runs in a `<vvjl-
lightbox>` custom element extending vvj_core's `VvjElementBase`.

## Features

- Image grid (configurable per-image width + gap)
- Modal with prev/next navigation
- Six animation variants for image transitions (fade, zoom, slide-from-
  top/bottom/left/right)
- Configurable overlay color + opacity
- Native focus trap (using `vvj_core/focus-trap`); ESC closes modal
- WCAG 2.2 AAA accessibility: keyboard nav, ARIA states, screen-reader
  announcements
- `[vvjl:field]` and `[vvjl:field:plain]` Views tokens for the **first row** in
  header/footer/empty text when *Use replacement tokens from the first row* is
  enabled
- Lazy IntersectionObserver hydration (lighthouse-100 by default)
- AbortController-tracked listeners — clean cleanup on AJAX page swaps
- View Transitions API wrapping every state change (graceful fallback)
- **Drupal admin help** — extended topic at `/admin/help/vvjl`

## Installation

```bash
composer require drupal/vvjl:^2.0
drush en vvjl
```

`composer` resolves `drupal/vvj_core` automatically.

## Usage

1. Create or edit a View.
2. Set **Format** to *Views Vanilla JavaScript Lightbox*.
3. Set **Show** to *Fields*.
4. The first field MUST be an image. Add additional fields for caption / overlay
   text (optional).
5. Configure grid + animation + overlay in the format settings.
6. Save.

The `vvjl_example` optional view (`config/optional/views.view.vvjl_example.yml`)
demonstrates a working configuration when your site can import it without an ID
collision.

## Drop-in upgrade from 1.x

`composer update drupal/vvjl && drush cache:rebuild` is sufficient. v2
preserves:

- Plugin ID `views_vvjl`
- Theme hook `views_view_vvjl`
- Twig template names
- All option keys (`grid_image_width`, `grid_image_gap`, `overlay_color`,
  `overlay_opacity`, `disable_overlay`, `animation`)
- Library names (`vvjl/vvjl`)
- JS behavior key `Drupal.behaviors.vvjLightbox`
- CSS class names (`.vvj-lightbox`, `.vvjl`, `.vvjl-inner`, `.lightbox-row`,
  `.lightbox-modal`, etc.)
- SVG icon paths (`svg/*.svg`)

Only one rendered-tag change: outer wrapper changed from `<div>` to `<vvjl-
lightbox>`. Theme selectors targeting `.vvjl` keep matching; `div.vvjl` (rare)
does not.

## Architecture

See [docs/planning/VVJ-CORE-
DESIGN.md](https://github.com/flashwebcenter/apex/blob/main/docs/planning/VVJ-
CORE-DESIGN.md) of the parent project for the full vvj_core foundation
architecture.

## License

GPL-2.0-or-later.

## Maintainer

Alaa Haddad —
[drupal.org/u/flashwebcenter](https://www.drupal.org/u/flashwebcenter)

Issues:
[drupal.org/project/issues/vvjl](https://www.drupal.org/project/issues/vvjl)
