# Changelog

## [2.0.0] (UNRELEASED)

**Drop-in upgrade for 1.x sites.** v2 modernizes internals: extends the new
`vvj_core` foundation, ships a `<vvjl-lightbox>` custom element extending
`VvjElementBase`, ports all behavior to AbortController-tracked listeners with
View Transitions support, while preserving every CSS rule, every option key,
the plugin ID, the theme hook, the Twig template names, and the JS behavior key
from 1.x.

### Added

- Patterns: the bridge reads the shared roles for the Prints; the chips, rail and current number keep the stage tokens.
- Pattern colors: every hard-coded Pattern color is a `--vvjl-*` token in `vvjl-pattern-base.css`; new `pattern-bridge-solo` and `pattern-bridge-apex` libraries (`patterns/bridge/`) map the button, menu and print colors to a Solo or APEX theme. The black-and-white look is unchanged.
- Patterns: 5 ready-made designs (Mosaic, Contact sheet, Reel, Prints, Index) chosen in the format settings. They need the `vvj_patterns` sub-module of `drupal/vvj_core`.
- `drupal/vvj_core` dependency. Composer pulls it automatically.
- `<vvjl-lightbox>` custom element (extends `VvjElementBase`). Lazy
  IntersectionObserver hydration; AbortController-tracked listeners; View
  Transitions wrapper around modal open/navigate; reduced-motion awareness.
- JS focus trap via `vvj_core/focus-trap` on the `role="dialog"` modal.

### Fixed

- Patterns: muted meta text (Prints under Solo and APEX, and the Sheet) uses 88% of the ink (was 70% and 80%), so it keeps a 4.5:1 contrast.
- Accessibility: the lightbox region has one name, its hidden heading; with a Pattern the heading reads the Pattern label (the unused `aria-label` is gone).
- Accessibility: `aria-expanded` is now set on the open button (the control) instead of the row group; every view's dialog is `aria-modal`; heading and counter ids carry the view id, so two lightboxes on one page no longer share ids; the open button's unused hidden text is removed.
- Accessibility: the copy of a photo shown in the viewer no longer carries the grid's open button, which Patterns made visible and focusable there although it did nothing.
- Viewer buttons: Pattern icons follow the button size and color instead of the module 60px white icon (Mosaic, Sheet and Reel showed oversized or white-on-white arrows). Mosaic: the bento repeats every six photos, so photos 8 and on no longer fall into 1/12-width slivers.
- Hover rules that must beat a rest rule are flattened with `:where()` so no new Pattern selector exceeds 0,2,0; the look is unchanged (check-vvj-cascade: winners changed 0).
- Update 10002 converts v1 views to the v2 option shape (value types:
  strings and integers stored where v2 declares integer, boolean and float).
- **Escape closes the open modal again**: the v2 focus-trap helper only handles
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
- Code style: JavaScript and CSS follow Drupal core's ESLint, Stylelint and Prettier rules, and the spell-check job passes with a project word file. Rendering and behavior are unchanged.
- Layout: Pattern layout now follows the width the Pattern itself gets (container queries on `vvj-region`) instead of the screen width, so Patterns lay out correctly in a narrow content column; overlays and the Pattern root keep screen-width queries.

### Removed

- `src/Twig/VVJLTwigExtension.php`: `safe_html` filter moved to `vvj_core`.
- Cross-cutting constants (token pattern, unique-ID range), moved to
  `vvj_core`'s `ValidationBounds`.
- Procedural hooks in `vvjl.module`.

### Drupal core / PHP

- Requires Drupal `^11.3 || ^12` (was `^10 || ^11`).
- Requires PHP `>=8.3`.

### Migration guide

`composer update drupal/vvjl` + `drush cache:rebuild`. Existing views, theme
overrides, custom CSS, and custom JS continue working.
