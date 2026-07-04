# Changelog

## [2.0.0] — UNRELEASED

**Drop-in upgrade for 1.x sites.** v2 modernizes internals — extends the new
`vvj_core` foundation, ships a `<vvjl-lightbox>` custom element extending
`VvjElementBase`, ports all behavior to AbortController-tracked listeners with
View Transitions support — while preserving every CSS rule, every option key,
the plugin ID, the theme hook, the Twig template names, and the JS behavior key
from 1.x.

### Added

- `drupal/vvj_core` dependency. Composer pulls it automatically.
- `<vvjl-lightbox>` custom element (extends `VvjElementBase`). Lazy
  IntersectionObserver hydration; AbortController-tracked listeners; View
  Transitions wrapper around modal open/navigate; reduced-motion awareness.
- JS focus trap via `vvj_core/focus-trap` on the `role="dialog"` modal.

### Fixed

- **Escape closes the open modal again** — the v2 focus-trap helper only handles
  Tab, so Escape-to-close (a v1 keyboard feature) had been silently dropped.
  Restored via a key handler on the element.
- Overlay Opacity slider regained its live numeric value readout in the Views
  options form (the `#suffix`/`oninput` mirror v1 shipped).

### Changed

- `vvjl.module` is a stub. Hooks live as OOP `#[Hook]` classes under
  `src/Hook/`.
- Plugin extends `\Drupal\vvj_core\Plugin\views\style\VvjStylePluginBase`.
  Shared boilerplate (form weights, warning, advanced section, token doc,
  render, validate, library list, unique-id) inherited from the foundation.
- Token resolution delegates to `vvj_core.token_resolver`. `[vvjl:field]`
  semantics preserved.

### Removed

- `src/Twig/VVJLTwigExtension.php` — `safe_html` filter moved to `vvj_core`.
- Cross-cutting constants (token pattern, unique-ID range) — moved to
  `vvj_core`'s `ValidationBounds`.
- Procedural hooks in `vvjl.module`.

### Drupal core / PHP

- Requires Drupal `^11.3 || ^12` (was `^10 || ^11`).
- Requires PHP `>=8.3`.

### Migration guide

`composer update drupal/vvjl` + `drush cache:rebuild`. Existing views, theme
overrides, custom CSS, and custom JS continue working.
