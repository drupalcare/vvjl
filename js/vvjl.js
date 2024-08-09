/**
 * @file
 * VVJ Lightbox.
 *
 * Filename:     vvjl.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, once) => {
  'use strict';
  Drupal.behaviors.vvjLightbox = {
    attach: (context, settings) => {
      const currModal = document.querySelector('.lightbox-modal');
      const modalContent = currModal.querySelector('.lightbox-modal-content');
      const currentImageSpan = document.getElementById('current-image');
      let currentIndex = -1;
      let items = Array.from(document.querySelectorAll('.lightbox-row'));
      let lastFocusedElement;

      const updateItemCountClass = () => {
        const wrappers = document.querySelectorAll('.vvjl-inner');
        wrappers.forEach(wrapper => {
          const itemCount = wrapper.children.length - 1;
          wrapper.className = wrapper.className.replace(/\bgrid-count-\d+\b/g, '');
          wrapper.classList.add(`grid-count-${itemCount}`);
        });
      };

      const updateModalContent = () => {
        const clonedElement = items[currentIndex].cloneNode(true);
        const imageElement = clonedElement.querySelector('.lightbox-image img');
        if (imageElement) {
          const fullImageUrl = imageElement.getAttribute('src');
          imageElement.src = fullImageUrl;
          imageElement.removeAttribute('height');
          imageElement.removeAttribute('width');
        }
        modalContent.innerHTML = '';
        modalContent.appendChild(clonedElement);
        modalContent.offsetHeight;
        modalContent.style.opacity = '1';
        modalContent.classList.remove('fade-in', 'fade-out');
        requestAnimationFrame(() => {
          modalContent.classList.add('fade-in');
        });
        updateCurrentImageIndex();
      };

      const updateCurrentImageIndex = () => {
        currentImageSpan.textContent = currentIndex + 1;
      };

      const openModal = (event) => {
        lastFocusedElement = document.activeElement;
        const lightboxRow = event.currentTarget.closest('.lightbox-row');
        currentIndex = items.indexOf(lightboxRow);
        updateModalContent();
        lightboxRow.setAttribute('aria-expanded', 'true');
        currModal.setAttribute('aria-hidden', 'false');
        currModal.style.display = 'block';
        currModal.focus();
        document.addEventListener('keydown', trapFocus);
        event.stopPropagation();
      };

const closeModal = (event) => {
  // Trigger fade-out animation
  modalContent.classList.remove('fade-in');
  modalContent.classList.add('fade-out');

  // Use setTimeout to wait for the animation to complete before hiding the modal
  setTimeout(() => {
    currModal.style.display = 'none';
    currModal.setAttribute('aria-hidden', 'true');
    document.querySelectorAll('.lightbox-row[aria-expanded="true"]').forEach(row => row.setAttribute('aria-expanded', 'false'));
    currentIndex = -1;
    if (lastFocusedElement) {
      lastFocusedElement.focus();
    }
    document.removeEventListener('keydown', trapFocus);

    // Reset the animation classes
    modalContent.classList.remove('fade-out');
  }, 500); // The timeout should match the duration of the fade-out animation (0.5s = 500ms)

  if (event) event.stopPropagation();
};


      const navigateModal = (direction) => {
        document.querySelectorAll('.lightbox-row[aria-expanded="true"]').forEach(row => row.setAttribute('aria-expanded', 'false'));

        if (direction === 'next') {
          currentIndex = (currentIndex + 1) % items.length;
        } else if (direction === 'prev') {
          currentIndex = (currentIndex - 1 + items.length) % items.length;
        }

        items[currentIndex].setAttribute('aria-expanded', 'true');
        updateModalContent();
      };

      const trapFocus = (event) => {
        const focusableElements = currModal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.key === 'Tab') {
          if (event.shiftKey) {
            if (document.activeElement === firstElement) {
              lastElement.focus();
              event.preventDefault();
            }
          } else {
            if (document.activeElement === lastElement) {
              firstElement.focus();
              event.preventDefault();
            }
          }
        } else if (event.key === 'Escape') {
          closeModal(event);
        }
      };

      once('modalExpand', '.lightbox-row', context).forEach(row => row.addEventListener('click', openModal));
      once('modalNavigation', '.modal-next, .modal-prev', context).forEach(button => button.addEventListener('click', event => {
        const direction = button.classList.contains('modal-next') ? 'next' : 'prev';
        navigateModal(direction);
        event.stopPropagation();
      }));

      context.addEventListener('click', event => {
        if (event.target.matches('.modal-close') || event.target.closest('.modal-close') || event.target === modalContent) {
          closeModal(event);
        }
      });

      window.addEventListener('load', updateItemCountClass);
    }
  };
})(Drupal, once);



