/**
 * @file
 * Shared by the vvjl Pattern views: the navigation rail in the viewer.
 *
 * One button per row (thumbs, segments or numbers, printed by the
 * template). Roving focus with Left/Right/Home/End (vvj_core keyboard-nav),
 * Enter or click shows that image, and `aria-current` follows the shared
 * state contract (`vvj:change`). The thumbnail variant copies each row's
 * image into its button. Without JavaScript the viewer does not open, so
 * nothing here is needed then.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */

((Drupal, once) => {
  const controllers = new WeakMap();
  const KEYS = ['ArrowLeft', 'ArrowRight', 'Home', 'End'];

  const wire = (root, signal) => {
    const rail = root.querySelector('.lightbox-modal .vvjl-rail');
    if (!rail) {
      return;
    }
    const buttons = Array.from(rail.querySelectorAll('.vvjl-rail-btn'));
    const rows = root.querySelectorAll(':scope > .vvjl-inner > .lightbox-row');

    if (rail.classList.contains('vvjl-rail-thumbs')) {
      buttons.forEach((button, i) => {
        const face = button.querySelector('.vvjl-rail-thumb');
        const img = rows[i]?.querySelector('.lightbox-image img');
        if (!face || !img || face.firstElementChild) {
          return;
        }
        const copy = /** @type {HTMLImageElement} */ (img.cloneNode(false));
        copy.removeAttribute('id');
        copy.removeAttribute('width');
        copy.removeAttribute('height');
        copy.alt = '';
        copy.loading = 'lazy';
        face.append(copy);
      });
    }

    buttons.forEach((button, i) => {
      button.setAttribute('tabindex', i === 0 ? '0' : '-1');
      button.addEventListener(
        'click',
        (event) => {
          event.stopPropagation();
          root.showImage?.(i);
        },
        { signal },
      );
      button.addEventListener(
        'keydown',
        (event) => {
          if (!KEYS.includes(event.key)) {
            return;
          }
          // The viewer's own Left/Right must not also change the image.
          event.stopPropagation();
          Drupal.Vvj.handleArrowKeyNav(event, buttons, i, { horizontal: true });
        },
        { signal },
      );
    });

    Drupal.Vvj.onState(
      root,
      ({ index }) => {
        buttons.forEach((button, i) => {
          if (i === index) {
            button.setAttribute('aria-current', 'true');
            button.setAttribute('tabindex', '0');
          } else {
            button.removeAttribute('aria-current');
            button.setAttribute('tabindex', '-1');
          }
        });
      },
      signal,
    );
  };

  Drupal.behaviors.vvjPatternVvjl = {
    attach(context) {
      once('vvj-pattern-vvjl', 'vvjl-lightbox.vvj-pattern', context).forEach(
        (root) => {
          const controller = new AbortController();
          controllers.set(root, controller);
          Drupal.Vvj.whenReady(
            root,
            () => wire(root, controller.signal),
            controller.signal,
          );
        },
      );
    },
    detach(context, settings, trigger) {
      if (trigger !== 'unload') {
        return;
      }
      once
        .remove('vvj-pattern-vvjl', 'vvjl-lightbox.vvj-pattern', context)
        .forEach((root) => {
          controllers.get(root)?.abort();
          controllers.delete(root);
        });
    },
  };
})(Drupal, once);
