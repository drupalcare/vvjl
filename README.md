# Views Vanilla JavaScript Lightbox (VVJL)

A vanilla-JavaScript lightbox **Views format** for Drupal. Renders
Views rows as a clickable image grid; clicking an image opens a modal with
previous/next navigation and overlay color/opacity controls.

v2.0 builds on the **`drupal/vvj_core` foundation**: composer auto-installs it
on `composer update drupal/vvjl`. The lightbox behavior runs in a `<vvjl-lightbox>` custom element extending vvj_core's `VvjElementBase`.

## Table of contents

- Overview
- Requirements
- Installation
- Configuration
- Patterns
- Usage
- Troubleshooting
- Changelog
- Maintainers
- License

## Overview

### Features

- Image grid (configurable per-image width + gap)
- Modal with prev/next navigation
- Six animation variants for image transitions (fade, zoom, slide-from-top/bottom/left/right)
- Configurable overlay color + opacity
- Native focus trap (using `vvj_core/focus-trap`); ESC closes modal
- WCAG 2.2 AAA accessibility: keyboard nav, ARIA states, screen-reader
  announcements
- `[vvjl:field]` and `[vvjl:field:plain]` Views tokens for the **first row** in
  header/footer/empty text when *Use replacement tokens from the first row* is
  enabled
- Lazy IntersectionObserver hydration
- AbortController-tracked listeners: clean cleanup on AJAX page swaps
- View Transitions API wrapping every state change (graceful fallback)
- **Drupal admin help**: extended topic at `/admin/help/vvjl`

## Requirements

- Drupal core `^11.3 || ^12`
- PHP 8.3 or later
- Drupal core modules Views (`drupal:views`) and Filter (`drupal:filter`)
- [VVJ Core](https://www.drupal.org/project/vvj_core) (`drupal/vvj_core` `^2.0.1`), which composer installs for you

Optional: the `vvj_patterns` sub-module of `drupal/vvj_core` adds the Pattern select to the format settings (see [Patterns](#patterns)), and `vvj_patterns_demo` adds example content.

## Installation

```bash
composer require drupal/vvjl:^2.0
drush en vvjl
```

`composer` resolves `drupal/vvj_core` automatically.

### Upgrading from 1.x

`composer update drupal/vvjl && drush updb && drush cr` is sufficient. v2
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

Only one rendered-tag change: outer wrapper changed from `<div>` to `<vvjl-lightbox>`. Theme selectors targeting `.vvjl` keep matching; `div.vvjl` (rare)
does not.

`drush updb` enables `vvj_core` (`vvjl_update_10001`) and converts existing views to the v2 option shape (`vvjl_update_10002`). Without `vvj_core` on disk the update stops with a message telling you to download `drupal/vvj_core` and run `drush updb` again.

## Configuration

All settings live in the format settings of the *Views Vanilla JavaScript Lightbox* style on a View. They are grouped into grid, animation and overlay sections, and a **Patterns** section when `vvj_patterns` is enabled. The extended help topic is at `/admin/help/vvjl`.

Settings are stored in the View itself, as options of the `views_vvjl` style plugin; their types are declared in `config/schema/vvjl.schema.yml`.

## Patterns

A Pattern is a ready-made design for this format. VVJL ships 5. Enable the `vvj_patterns` sub-module of `drupal/vvj_core` (`drush en vvj_patterns`), open the format settings and choose one in **Patterns** > **Pattern**.

| Pattern | Look |
|---|---|
| Mosaic | A bento grid of photos; the tile photo grows into a dark viewer with round arrows. |
| Contact sheet | Even 3:2 frames with frame numbers; the viewer is a split screen with a paper sidebar and thumbnails. |
| Reel | One sideways row of arched tiles; a light viewer with story bars on top. |
| Prints | Tilted instant prints that straighten on hover; the print grows into a viewer with a bottom bar. |
| Index | A numbered list of rows; a black viewer with a numbered control. |

When you choose a different Pattern and save, its settings replace the layout, animation, styling and deep linking settings of the view; you can still change them afterwards. Choose **None** to keep your own settings. Drupal then shows a message naming the settings the Pattern set.

Each Pattern also lists what the view still needs, for example the fields to add, their order and the CSS classes to give them. The list appears under **To finish the Pattern** in the Patterns section and as a warning after you save. The `vvj_patterns_demo` sub-module shows a working setup: it adds the "VVJ Slide" content type (fields `vvj_nav_label`, `vvj_image`, `vvj_kicker`, `vvj_summary` and `vvj_link`, besides the title), five demo slides and, as optional config, an example view for each VVJ module named `vvj_demo_<module>`.

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

### Tokens in Views text areas

In Views headers, footers, or empty text with *Use replacement tokens from the
first row*, default Twig tokens (`{{ title }}`) do not work. Use VVJL tokens
instead:

- `{{ title }}` → `[vvjl:title]`
- `{{ field_image }}` → `[vvjl:field_image]`
- Append `:plain` for plain text: `[vvjl:title:plain]`

v2 resolves tokens through the shared `vvj_core.token_resolver` service; syntax
is unchanged from v1. Tokens read from the **first row** of rendered View
fields. Complex field rewrites are not supported.

## Troubleshooting

- **There is no Patterns section.** The section appears only while `vvj_patterns` is enabled. Run `drush en vvj_patterns`.
- **Saving the view changed my settings.** Choosing a different Pattern replaces the layout, animation, styling and deep linking settings. Choose **None** to keep your own.
- **A Pattern looks unfinished.** Follow the list under **To finish the Pattern**: the fields, their order and the CSS classes the Pattern needs.
- **After upgrading from 1.x the view fails or `vvj_core` is not enabled.** Run `drush updb`; update `vvjl_update_10001` enables `vvj_core`. Composer installs it automatically, and a manual install needs `drupal/vvj_core` in `modules/contrib/vvj_core` first.
- **Test errors that name missing infrastructure.** See *Running the tests* in [CONTRIBUTING.md](./CONTRIBUTING.md).
- **Anything else.** Search or open an issue in the [issue queue](https://www.drupal.org/project/issues/vvjl).

## Changelog

See [CHANGELOG.md](./CHANGELOG.md).

## Maintainers

- Alaa Haddad - [flashwebcenter](https://www.drupal.org/u/flashwebcenter)

## License

GPL-2.0-or-later.
