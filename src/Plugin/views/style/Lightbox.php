<?php

declare(strict_types=1);

namespace Drupal\vvjl\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsStyle;
use Drupal\vvj_core\Plugin\views\style\VvjStylePluginBase;
use Drupal\vvjl\VvjlConstants;

/**
 * Renders Views rows as a vanilla-JavaScript lightbox.
 *
 * @ingroup views_style_plugins
 */
#[ViewsStyle(
  id: 'views_vvjl',
  title: new TranslatableMarkup('Views Vanilla JavaScript Lightbox'),
  help: new TranslatableMarkup('Render items in Lightbox using vanilla JavaScript.'),
  theme: 'views_view_vvjl',
  display_types: ['normal'],
)]
class Lightbox extends VvjStylePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleSlug(): string {
    return 'vvjl';
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomElementTag(): string {
    return 'vvjl-lightbox';
  }

  /**
   * {@inheritdoc}
   *
   * Lightbox doesn't expose deep linking (modal state is transient).
   */
  protected function supportsDeeplinking(): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * Vvjl does not ship a `vvjl-style` library; the main `vvjl` library
   * contains the required CSS and JavaScript.
   */
  protected function supportsEnableCss(): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function definePatternOptions(array $options): array {
    $options['grid_image_width'] = ['default' => VvjlConstants::DEFAULT_GRID_IMAGE_WIDTH];
    $options['grid_image_gap'] = ['default' => VvjlConstants::DEFAULT_GRID_IMAGE_GAP];
    $options['overlay_color'] = ['default' => VvjlConstants::DEFAULT_OVERLAY_COLOR];
    $options['overlay_opacity'] = ['default' => VvjlConstants::DEFAULT_OVERLAY_OPACITY];
    $options['disable_overlay'] = ['default' => VvjlConstants::DEFAULT_DISABLE_OVERLAY];
    $options['animation'] = ['default' => VvjlConstants::DEFAULT_ANIMATION];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getAnimationPresets(): array {
    return [
      VvjlConstants::ANIMATION_NONE => $this->t('None'),
      VvjlConstants::ANIMATION_ZOOM => $this->t('Zoom'),
      VvjlConstants::ANIMATION_TOP => $this->t('Slide from Top'),
      VvjlConstants::ANIMATION_RIGHT => $this->t('Slide from Right'),
      VvjlConstants::ANIMATION_BOTTOM => $this->t('Slide from Bottom'),
      VvjlConstants::ANIMATION_LEFT => $this->t('Slide from Left'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function buildPatternSections(array &$form): void {
    $this->buildGridSection($form);
    $this->buildAnimationSection($form);
    $this->buildOverlaySection($form);
  }

  /**
   * Override warning message — lightbox needs an image as first field.
   *
   * @param array<string, mixed> $form
   *   The form, modified in place.
   */
  protected function buildWarningMessage(array &$form): void {
    if ($this->view->storage->id() === 'vvjl_example') {
      return;
    }
    $form['warning_message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--warning">' . $this->t(
        'Note: The first field must be an image. To see an example, check the <a href="/admin/structure/views/view/vvjl_example">vvjl_example</a> view.',
      ) . '</div>',
      '#weight' => -50,
    ];
  }

  /**
   * Grid section — image width + gap.
   *
   * @param array<int|string, mixed> $form
   *   The form, modified in place.
   */
  protected function buildGridSection(array &$form): void {
    $form['grid_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Grid Settings'),
      '#open' => TRUE,
      '#weight' => -40,
    ];

    $form['grid_section']['grid_image_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Grid Image Width (px)'),
      '#default_value' => $this->options['grid_image_width'],
      '#description' => $this->t('Set the width of the grid image in pixels.'),
      '#step' => 1,
      '#min' => VvjlConstants::MIN_GRID_IMAGE_WIDTH,
      '#required' => TRUE,
    ];

    $form['grid_section']['grid_image_gap'] = [
      '#type' => 'number',
      '#title' => $this->t('Grid Image Gap (px)'),
      '#default_value' => $this->options['grid_image_gap'],
      '#description' => $this->t('Set the gap between grid images in pixels.'),
      '#step' => 1,
      '#min' => VvjlConstants::MIN_GRID_IMAGE_GAP,
      '#required' => TRUE,
    ];
  }

  /**
   * Animation section — uses parent's buildAnimationSelect helper.
   *
   * @param array<int|string, mixed> $form
   *   The form, modified in place.
   */
  protected function buildAnimationSection(array &$form): void {
    $form['animation_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Animation Settings'),
      '#open' => TRUE,
      '#weight' => -30,
    ];

    $form['animation_section']['animation'] = [
      '#title' => $this->t('Animation Type'),
      '#description' => $this->t('Choose the animation type to apply when click next/prev in the modal. Reduced-motion users see no animation regardless of this setting.'),
    ] + $this->buildAnimationSelect('animation');
  }

  /**
   * Overlay section — color + opacity + disable toggle.
   *
   * @param array<int|string, mixed> $form
   *   The form, modified in place.
   */
  protected function buildOverlaySection(array &$form): void {
    $form['overlay_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Overlay Settings'),
      '#open' => TRUE,
      '#weight' => -20,
    ];

    $form['overlay_section']['overlay_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Overlay Color'),
      '#default_value' => $this->options['overlay_color'],
      '#description' => $this->t('Choose a color overlay for the lightbox background.'),
      '#states' => [
        'disabled' => [
          ':input[name="style_options[overlay_section][disable_overlay]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $raw_overlay_opacity = $this->options['overlay_opacity'] ?? '';
    $overlay_opacity_display = is_scalar($raw_overlay_opacity)
      ? (string) $raw_overlay_opacity
      : '';
    $form['overlay_section']['overlay_opacity'] = [
      '#type' => 'range',
      '#title' => $this->t('Overlay Opacity'),
      '#default_value' => $this->options['overlay_opacity'],
      '#min' => VvjlConstants::MIN_OPACITY,
      '#max' => VvjlConstants::MAX_OPACITY,
      '#step' => VvjlConstants::OPACITY_STEP,
      '#description' => $this->t('Set the opacity level for the overlay.'),
      '#suffix' => '<span id="overlay-opacity-value" class="opacity-value">'
      . $overlay_opacity_display . '</span>',
      '#attributes' => [
        'oninput' => 'document.getElementById("overlay-opacity-value").innerText = this.value;',
      ],
      '#states' => [
        'disabled' => [
          ':input[name="style_options[overlay_section][disable_overlay]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['overlay_section']['disable_overlay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Overlay Color'),
      '#default_value' => $this->options['disable_overlay'],
      '#description' => $this->t('Check this to disable overlay color completely.'),
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @param array<int|string, mixed> $form
   *   The Views style options form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state): void {
    $values = $form_state->getValue('style_options');
    if (!is_array($values)) {
      $values = [];
    }
    $form_state->setValue('style_options', $this->flattenFormValues($values));
    parent::submitOptionsForm($form, $form_state);
  }

  /**
   * Flatten the sectioned form values back into the persisted shape.
   *
   * @param array<int|string, mixed> $values
   *   Submitted form values keyed by section then by option name.
   *
   * @return array<string, mixed>
   *   Flattened option array suitable for $this->options.
   */
  protected function flattenFormValues(array $values): array {
    $flattened = [];

    $grid = $values['grid_section'] ?? NULL;
    if (is_array($grid)) {
      $flattened['grid_image_width'] = $grid['grid_image_width'] ?? VvjlConstants::DEFAULT_GRID_IMAGE_WIDTH;
      $flattened['grid_image_gap'] = $grid['grid_image_gap'] ?? VvjlConstants::DEFAULT_GRID_IMAGE_GAP;
    }

    $animation = $values['animation_section'] ?? NULL;
    if (is_array($animation)) {
      $flattened['animation'] = $animation['animation'] ?? VvjlConstants::DEFAULT_ANIMATION;
    }

    $overlay = $values['overlay_section'] ?? NULL;
    if (is_array($overlay)) {
      $flattened['overlay_color'] = $overlay['overlay_color'] ?? VvjlConstants::DEFAULT_OVERLAY_COLOR;
      $flattened['overlay_opacity'] = $overlay['overlay_opacity'] ?? VvjlConstants::DEFAULT_OVERLAY_OPACITY;
      $flattened['disable_overlay'] = $overlay['disable_overlay'] ?? VvjlConstants::DEFAULT_DISABLE_OVERLAY;
    }

    $advanced = $values['advanced_section'] ?? NULL;
    if (is_array($advanced)) {
      $flattened['enable_css'] = $advanced['enable_css'] ?? TRUE;
    }

    $flattened['unique_id'] = $this->options['unique_id'] ?? $this->generateUniqueId();

    return $flattened;
  }

}
