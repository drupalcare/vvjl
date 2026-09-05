/**
 * @file
 * VVJL compat shim. Behavior lives in `<vvjl-lightbox>` custom element
 * (see `vvjl-lightbox-element.js`).
 *
 * Registers `Drupal.behaviors.vvjLightbox` as a no-op for v1 sites
 * that referenced it from custom code.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */

// phpcs:disable Drupal.NamingConventions.UpperCaseConstant
// phpcs:disable Generic.PHP.UpperCaseConstant
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing

((Drupal, once) => {
  Drupal.behaviors.vvjLightbox = {
    attach(context) {
      // No-op marker. Custom element hydrates itself via
      // connectedCallback() → IntersectionObserver.
      once('vvjl-lightbox-marker', 'vvjl-lightbox', context);
    },
    detach() {
      // Custom element's disconnectedCallback aborts every listener.
    },
  };
})(Drupal, once);
