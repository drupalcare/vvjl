/**
 * @file
 * VvjlLightboxElement — `<vvjl-lightbox>` custom element.
 *
 * Extends `Drupal.Vvj.ElementBase`. The lightbox modal is a
 * `role="dialog"` container with a JS focus trap (`Drupal.Vvj.trapFocus`)
 * and an Escape-to-close key handler; the element coordinates the grid →
 * modal transition, navigation between images, and aria state. The base class + shared helpers are pulled
 * from the `Drupal.Vvj` namespace — Drupal core's cross-file sharing
 * pattern — with load order guaranteed by the `vvjl` library's
 * `dependencies:`.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */

// phpcs:disable Drupal.NamingConventions.UpperCaseConstant
// phpcs:disable Generic.PHP.UpperCaseConstant
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing

((Drupal) => {
  const { ElementBase, trapFocus } = Drupal.Vvj;

  class VvjlLightboxElement extends ElementBase {
    static get patternSlug() {
      return 'vvjl';
    }

    onHydrate() {
      /** @type {HTMLElement | null} */
      this._inner = this.querySelector(':scope > .vvjl-inner');
      if (!this._inner) {
        return;
      }

      /** @type {HTMLElement | null} */
      this._modal = this._inner.querySelector('.lightbox-modal');
      /** @type {HTMLElement | null} */
      this._modalContent =
        this._modal?.querySelector('.lightbox-modal-content') ?? null;
      /** @type {HTMLElement | null} */
      this._currentImageSpan = this._inner.querySelector('#current-image');
      /** @type {NodeListOf<HTMLElement>} */
      this._items = this._inner.querySelectorAll('.lightbox-row');
      /** @type {number} */
      this._currentIndex = -1;
      /** @type {Element | null} */
      this._lastFocusedElement = null;
      /** @type {(() => void) | null} */
      this._releaseFocusTrap = null;

      this._updateItemCountClass();
      this._initAriaAttributes();
      this._wireGridClicks();
      this._wireNavigationButtons();
      this._wireCloseClicks();
      this._wireEscapeKey();
      this._neutralizeGridLinks(true);
    }

    onDisconnect() {
      this._releaseFocusTrap?.();
      this._releaseFocusTrap = null;
    }

    // -----------------------------------------------------------------
    // Initial DOM state.
    // -----------------------------------------------------------------

    _updateItemCountClass() {
      if (!this._inner) {
        return;
      }
      // Total grid children minus 1 for the modal itself.
      const itemCount = Math.max(0, this._inner.children.length - 1);
      this._inner.className = this._inner.className
        .replace(/\bgrid-count-\d+\b/g, '')
        .trim();
      this._inner.classList.add(`grid-count-${itemCount}`);
    }

    _initAriaAttributes() {
      if (!this._modal) {
        return;
      }
      this._items.forEach((item) => {
        item.setAttribute('aria-expanded', 'false');
      });
      this._modal.setAttribute('aria-hidden', 'true');
    }

    // -----------------------------------------------------------------
    // Listeners.
    // -----------------------------------------------------------------

    _wireGridClicks() {
      this._items.forEach((row) => {
        row.addEventListener('click', this._handleRowClick, {
          signal: this.signal,
        });
      });
    }

    _wireNavigationButtons() {
      if (!this._modal) {
        return;
      }
      const prev = this._modal.querySelector('.modal-prev');
      const next = this._modal.querySelector('.modal-next');
      prev?.addEventListener(
        'click',
        (event) => {
          this._navigate('prev');
          event.stopPropagation();
        },
        { signal: this.signal },
      );
      next?.addEventListener(
        'click',
        (event) => {
          this._navigate('next');
          event.stopPropagation();
        },
        { signal: this.signal },
      );
    }

    _wireCloseClicks() {
      if (!this._modal) {
        return;
      }
      this.addEventListener(
        'click',
        (event) => {
          const target = /** @type {HTMLElement} */ (event.target);
          if (target.closest('.modal-close') || target === this._modalContent) {
            this._closeModal();
          }
        },
        { signal: this.signal },
      );
    }

    /**
     * Close the modal on the Escape key while it is open (v1 parity).
     *
     * Attached once at document level (signal-scoped so it is removed on
     * disconnect); acts only when the modal is currently open.
     */
    _wireEscapeKey() {
      document.addEventListener(
        'keydown',
        (event) => {
          if (event.key !== 'Escape' || !this._modal) {
            return;
          }
          if (this._modal.getAttribute('aria-hidden') === 'false') {
            this._closeModal();
          }
        },
        { signal: this.signal },
      );
    }

    _neutralizeGridLinks(disable) {
      this._items.forEach((item) => {
        const links = item.querySelectorAll('a');
        links.forEach((/** @type {HTMLAnchorElement} */ link) => {
          if (disable) {
            link.setAttribute('tabindex', '-1');
            link.addEventListener('click', this._preventDefault, {
              signal: this.signal,
            });
          } else {
            link.removeAttribute('tabindex');
          }
        });
      });
    }

    _preventDefault = (event) => event.preventDefault();

    // -----------------------------------------------------------------
    // Modal open / close / navigate.
    // -----------------------------------------------------------------

    _handleRowClick = (event) => {
      /** @type {HTMLElement | null} */
      const lightboxRow = /** @type {HTMLElement} */ (event.currentTarget);
      if (!this._modal || !this._items.length) {
        return;
      }
      this._lastFocusedElement = document.activeElement;
      this._currentIndex = Array.from(this._items).indexOf(lightboxRow);
      this._updateModalContent();
      lightboxRow.setAttribute('aria-expanded', 'true');
      this._modal.setAttribute('aria-hidden', 'false');
      this._modal.style.display = 'block';

      this.withTransition(() => {
        this._modal?.focus();
        this._releaseFocusTrap = trapFocus(this._modal, this.signal);
      });

      event.stopPropagation();
      this._neutralizeGridLinks(false);
    };

    _closeModal() {
      if (!this._modal || !this._modalContent) {
        return;
      }
      this._modalContent.classList.remove('fade-in');
      this._modalContent.classList.add('fade-out');

      setTimeout(() => {
        if (!this._modal || !this._modalContent) {
          return;
        }
        this._modal.style.display = 'none';
        this._modal.setAttribute('aria-hidden', 'true');
        this._inner
          ?.querySelectorAll('.lightbox-row[aria-expanded="true"]')
          .forEach((row) => {
            row.setAttribute('aria-expanded', 'false');
          });
        this._currentIndex = -1;
        if (this._lastFocusedElement instanceof HTMLElement) {
          this._lastFocusedElement.focus();
        }
        this._releaseFocusTrap?.();
        this._releaseFocusTrap = null;
        this._modalContent.classList.remove('fade-out');
        this._neutralizeGridLinks(true);
      }, 500);
    }

    /** @param {'next' | 'prev'} direction */
    _navigate(direction) {
      if (!this._items.length) {
        return;
      }
      this._inner
        ?.querySelectorAll('.lightbox-row[aria-expanded="true"]')
        .forEach((row) => {
          row.setAttribute('aria-expanded', 'false');
        });
      if (direction === 'next') {
        this._currentIndex = (this._currentIndex + 1) % this._items.length;
      } else {
        this._currentIndex =
          (this._currentIndex - 1 + this._items.length) % this._items.length;
      }
      this._items[this._currentIndex]?.setAttribute('aria-expanded', 'true');
      this.withTransition(() => this._updateModalContent());
    }

    _updateModalContent() {
      if (!this._modalContent || !this._items[this._currentIndex]) {
        return;
      }
      const cloned = /** @type {HTMLElement} */ (
        this._items[this._currentIndex].cloneNode(true)
      );
      /** @type {HTMLImageElement | null} */
      const imageElement = cloned.querySelector('.lightbox-image img');
      if (imageElement) {
        imageElement.src = imageElement.getAttribute('src') ?? '';
        imageElement.removeAttribute('height');
        imageElement.removeAttribute('width');
      }
      this._modalContent.innerHTML = '';
      this._modalContent.appendChild(cloned);
      // Force reflow so the fade-in animation runs each time.
      this._modalContent.getBoundingClientRect();
      this._modalContent.style.opacity = '1';
      this._modalContent.classList.remove('fade-in', 'fade-out');
      requestAnimationFrame(() => {
        this._modalContent?.classList.add('fade-in');
      });
      if (this._currentImageSpan) {
        this._currentImageSpan.textContent = String(this._currentIndex + 1);
      }
    }
  }

  if (!customElements.get('vvjl-lightbox')) {
    customElements.define('vvjl-lightbox', VvjlLightboxElement);
  }
})(Drupal);
