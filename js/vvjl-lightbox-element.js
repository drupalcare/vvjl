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

  /**
   * Sets aria-expanded on a row's opening button, never on the row itself.
   *
   * The row is a group, where aria-expanded is not supported.
   *
   * @param {HTMLElement | undefined} row
   *   A grid row.
   * @param {boolean} expanded
   *   TRUE while the viewer shows this row.
   */
  const setRowExpanded = (row, expanded) => {
    row
      ?.querySelector('.modal-open')
      ?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
  };

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
      this._currentImageSpan = this._inner.querySelector(
        '.lightbox-counter .current-image',
      );
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
      this._wirePatternGestures();
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
      this._collapseAll();
      this._modal.setAttribute('aria-hidden', 'true');
    }

    _collapseAll() {
      this._items.forEach((row) => setRowExpanded(row, false));
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

    /**
     * Pattern views only: Left/Right keys and swipe move between images.
     *
     * Keys inside the Pattern rail are left to the rail (roving focus).
     */
    _wirePatternGestures() {
      if (!this.classList.contains('vvj-pattern') || !this._modalContent) {
        return;
      }
      document.addEventListener(
        'keydown',
        (event) => {
          if (
            !this._modal ||
            this._modal.getAttribute('aria-hidden') !== 'false' ||
            (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') ||
            event.target?.closest?.('.vvjl-rail')
          ) {
            return;
          }
          event.preventDefault();
          this._navigate(event.key === 'ArrowRight' ? 'next' : 'prev');
        },
        { signal: this.signal },
      );
      let startX = null;
      const content = this._modalContent;
      content.addEventListener(
        'pointerdown',
        (event) => {
          startX = event.pointerType === 'mouse' ? null : event.clientX;
        },
        { signal: this.signal },
      );
      content.addEventListener(
        'pointercancel',
        () => {
          startX = null;
        },
        { signal: this.signal },
      );
      content.addEventListener(
        'pointerup',
        (event) => {
          if (startX === null) {
            return;
          }
          const dx = event.clientX - startX;
          startX = null;
          if (Math.abs(dx) < 40) {
            return;
          }
          // The click that follows a swipe must not close the viewer.
          const swallow = (click) => click.stopPropagation();
          content.addEventListener('click', swallow, {
            capture: true,
            once: true,
            signal: this.signal,
          });
          setTimeout(
            () =>
              content.removeEventListener('click', swallow, { capture: true }),
            400,
          );
          this._navigate(dx < 0 ? 'next' : 'prev');
        },
        { signal: this.signal },
      );
    }

    /**
     * Shows the image at `index` (used by the Pattern rail).
     *
     * @param {number} index
     *   Zero-based row index.
     */
    showImage(index) {
      if (
        !this._items?.length ||
        index < 0 ||
        index >= this._items.length ||
        index === this._currentIndex
      ) {
        return;
      }
      this._collapseAll();
      this._currentIndex = index;
      setRowExpanded(this._items[index], true);
      this.withTransition(() => this._updateModalContent());
    }

    /**
     * Pattern views with the morph feature, when View Transitions exist.
     *
     * @return {boolean}
     *   TRUE when the photo should morph between tile and viewer.
     */
    _canMorph() {
      return (
        this.classList.contains('vvjl-morph') &&
        typeof document.startViewTransition === 'function'
      );
    }

    /**
     * Names one image `vvjl-photo` for a View Transition, clears the others.
     *
     * @param {HTMLImageElement | null | undefined} img
     *   The image to name, or nothing to clear every name.
     */
    _namePhoto(img) {
      this.querySelectorAll('.lightbox-image img').forEach((el) => {
        /** @type {HTMLElement} */ (el).style.viewTransitionName = '';
      });
      if (img) {
        img.style.viewTransitionName = 'vvjl-photo';
      }
    }

    _modalImage() {
      return this._modalContent?.querySelector('.lightbox-image img') ?? null;
    }

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
      if (this._canMorph()) {
        // The tile photo morphs into the viewer photo (Pattern feature).
        this._namePhoto(lightboxRow.querySelector('.lightbox-image img'));
        const morph = this.withTransition(() => {
          this._updateModalContent();
          setRowExpanded(lightboxRow, true);
          if (this._modal) {
            this._modal.setAttribute('aria-hidden', 'false');
            this._modal.style.display = 'block';
          }
          this._namePhoto(this._modalImage());
        });
        if (morph) {
          morph.finished.finally(() => this._namePhoto(null));
        } else {
          this._namePhoto(null);
        }
        this._modal.focus();
        this._releaseFocusTrap = trapFocus(this._modal, this.signal);
        event.stopPropagation();
        this._neutralizeGridLinks(false);
        return;
      }
      this._updateModalContent();
      setRowExpanded(lightboxRow, true);
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
      if (this._canMorph() && this._currentIndex > -1) {
        // The viewer photo morphs back into its tile (Pattern feature).
        const tile = this._items[this._currentIndex]?.querySelector(
          '.lightbox-image img',
        );
        this._namePhoto(this._modalImage());
        const morph = this.withTransition(() => {
          this._finishClose();
          this._namePhoto(tile);
        });
        if (morph) {
          morph.finished.finally(() => this._namePhoto(null));
        } else {
          this._namePhoto(null);
        }
        return;
      }
      this._modalContent.classList.remove('fade-in');
      this._modalContent.classList.add('fade-out');

      setTimeout(() => this._finishClose(), 500);
    }

    _finishClose() {
      if (!this._modal || !this._modalContent) {
        return;
      }
      this._modal.style.display = 'none';
      this._modal.setAttribute('aria-hidden', 'true');
      this._collapseAll();
      this._currentIndex = -1;
      if (this._lastFocusedElement instanceof HTMLElement) {
        this._lastFocusedElement.focus();
      }
      this._releaseFocusTrap?.();
      this._releaseFocusTrap = null;
      this._modalContent.classList.remove('fade-out');
      this._neutralizeGridLinks(true);
    }

    /** @param {'next' | 'prev'} direction */
    _navigate(direction) {
      if (!this._items.length) {
        return;
      }
      this._collapseAll();
      if (direction === 'next') {
        this._currentIndex = (this._currentIndex + 1) % this._items.length;
      } else {
        this._currentIndex =
          (this._currentIndex - 1 + this._items.length) % this._items.length;
      }
      setRowExpanded(this._items[this._currentIndex], true);
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
      // The open button only works in the grid. Pattern CSS shows it again
      // inside the viewer, where it would be a focusable control that does
      // nothing, so the copy drops it.
      cloned.querySelectorAll('.modal-open').forEach((el) => el.remove());
      // Grid links are taken out of the tab order while the viewer is open,
      // and a copy made then would keep that. Links in the viewer are real
      // content, so the copy always restores them.
      cloned
        .querySelectorAll('a[href][tabindex="-1"]')
        .forEach((link) => link.removeAttribute('tabindex'));
      if (this.classList.contains('vvj-pattern')) {
        // Pattern views: the copy in the viewer must not repeat the ids.
        cloned.removeAttribute('id');
        cloned.removeAttribute('aria-labelledby');
        cloned
          .querySelectorAll('[id]')
          .forEach((el) => el.removeAttribute('id'));
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
      // Shared state contract; does nothing unless this is a Pattern view.
      this.writeState({
        index: this._currentIndex,
        total: this._items.length,
        items: this._items,
      });
    }
  }

  if (!customElements.get('vvjl-lightbox')) {
    customElements.define('vvjl-lightbox', VvjlLightboxElement);
  }
})(Drupal);
