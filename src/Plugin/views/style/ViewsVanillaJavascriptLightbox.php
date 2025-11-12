<?php

declare(strict_types=1);

namespace Drupal\vvjl\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\vvjl\VvjlConstants;

/**
 * Style plugin to render items in Lightbox using vanilla JavaScript.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_vvjl",
 *   title = @Translation("Views Vanilla JavaScript Lightbox"),
 *   help = @Translation("Render items in Lightbox using vanilla JavaScript."),
 *   theme = "views_view_vvjl",
 *   display_types = { "normal" }
 * )
 */
class ViewsVanillaJavascriptLightbox extends StylePluginBase {

  /**
   * Animation type constants.
   */
  public const ANIMATION_NONE = 'none';
  public const ANIMATION_ZOOM = 'a-zoom';
  public const ANIMATION_TOP = 'a-top';
  public const ANIMATION_RIGHT = 'a-right';
  public const ANIMATION_BOTTOM = 'a-bottom';
  public const ANIMATION_LEFT = 'a-left';

  /**
   * Does the style plugin use a row plugin.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $usesRowClass = TRUE;

  /**
   * Cached unique ID for this view display.
   *
   * @var int|null
   */
  protected ?int $cachedUniqueId = NULL;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions(): array {
    $options = parent::defineOptions();
    $options[VvjlConstants::OPTION_UNIQUE_ID] = ['default' => $this->generateUniqueId()];
    $options[VvjlConstants::OPTION_GRID_IMAGE_WIDTH] = ['default' => VvjlConstants::DEFAULT_GRID_IMAGE_WIDTH];
    $options[VvjlConstants::OPTION_GRID_IMAGE_GAP] = ['default' => VvjlConstants::DEFAULT_GRID_IMAGE_GAP];
    $options[VvjlConstants::OPTION_OVERLAY_COLOR] = ['default' => VvjlConstants::DEFAULT_OVERLAY_COLOR];
    $options[VvjlConstants::OPTION_OVERLAY_OPACITY] = ['default' => VvjlConstants::DEFAULT_OVERLAY_OPACITY];
    $options[VvjlConstants::OPTION_DISABLE_OVERLAY] = ['default' => VvjlConstants::DEFAULT_DISABLE_OVERLAY];
    $options[VvjlConstants::OPTION_ANIMATION] = ['default' => VvjlConstants::DEFAULT_ANIMATION];
    return $options;
  }

  /**
   * Gets available animation type options.
   *
   * @return array
   *   Array of animation options with translated labels.
   */
  protected function getAnimationOptions(): array {
    return [
      self::ANIMATION_NONE => $this->t('None'),
      self::ANIMATION_ZOOM => $this->t('Zoom'),
      self::ANIMATION_TOP => $this->t('Slide from Top'),
      self::ANIMATION_RIGHT => $this->t('Slide from Right'),
      self::ANIMATION_BOTTOM => $this->t('Slide from Bottom'),
      self::ANIMATION_LEFT => $this->t('Slide from Left'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);

    $this->setDefaultElementWeights($form);
    $this->buildWarningMessage($form);
    $this->buildGridSection($form);
    $this->buildAnimationSection($form);
    $this->buildOverlaySection($form);
    $this->buildTokenDocumentation($form);
  }

  /**
   * Set weights for default Drupal form elements.
   */
  protected function setDefaultElementWeights(array &$form): void {
    $default_elements = [
      'grouping' => -100,
      'row_class' => -90,
      'default_row_class' => -85,
      'uses_fields' => -80,
      'class' => -75,
      'wrapper_class' => -70,
    ];

    foreach ($default_elements as $element_key => $weight) {
      if (isset($form[$element_key])) {
        $form[$element_key]['#weight'] = $weight;
      }
    }
  }

  /**
   * Build warning message section.
   */
  protected function buildWarningMessage(array &$form): void {
    if ($this->view->storage->id() === 'vvjl_example') {
      return;
    }
    $form['warning_message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--warning">' . $this->t('Note: The first field must be an image. To see an example, check the vvjl_example view by clicking <a href="/admin/structure/views/view/@view_id" style="display: inline;">here</a> to edit it.', ['@view_id' => VvjlConstants::EXAMPLE_VIEW_ID]) . '</div>',
      '#weight' => -50,
    ];
  }

  /**
   * Build grid configuration section.
   */
  protected function buildGridSection(array &$form): void {
    $form['grid_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Grid Settings'),
      '#open' => TRUE,
      '#weight' => -40,
    ];

    $form['grid_section'][VvjlConstants::OPTION_GRID_IMAGE_WIDTH] = [
      '#type' => 'number',
      '#title' => $this->t('Grid Image Width (px)'),
      '#default_value' => $this->options[VvjlConstants::OPTION_GRID_IMAGE_WIDTH],
      '#description' => $this->t('Set the width of the grid image in pixels.'),
      '#step' => 1,
      '#min' => VvjlConstants::MIN_GRID_IMAGE_WIDTH,
      '#required' => TRUE,
    ];

    $form['grid_section'][VvjlConstants::OPTION_GRID_IMAGE_GAP] = [
      '#type' => 'number',
      '#title' => $this->t('Grid Image Gap (px)'),
      '#default_value' => $this->options[VvjlConstants::OPTION_GRID_IMAGE_GAP],
      '#description' => $this->t('Set the gap between grid images in pixels.'),
      '#step' => 1,
      '#min' => VvjlConstants::MIN_GRID_IMAGE_GAP,
      '#required' => TRUE,
    ];
  }

  /**
   * Build animation configuration section.
   */
  protected function buildAnimationSection(array &$form): void {
    $form['animation_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Animation Settings'),
      '#open' => TRUE,
      '#weight' => -30,
    ];

    $form['animation_section'][VvjlConstants::OPTION_ANIMATION] = [
      '#type' => 'select',
      '#title' => $this->t('Animation Type'),
      '#options' => $this->getAnimationOptions(),
      '#default_value' => $this->options[VvjlConstants::OPTION_ANIMATION],
      '#description' => $this->t('Choose the animation type to apply when click next/prev in the modal.'),
    ];
  }

  /**
   * Build overlay configuration section.
   */
  protected function buildOverlaySection(array &$form): void {
    $form['overlay_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Overlay Settings'),
      '#open' => TRUE,
      '#weight' => -20,
    ];

    $form['overlay_section'][VvjlConstants::OPTION_OVERLAY_COLOR] = [
      '#type' => 'color',
      '#title' => $this->t('Overlay Color'),
      '#default_value' => $this->options[VvjlConstants::OPTION_OVERLAY_COLOR],
      '#description' => $this->t('Choose a color overlay for the lightbox background.'),
      '#states' => [
        'disabled' => [
          ':input[name="style_options[overlay_section][' . VvjlConstants::OPTION_DISABLE_OVERLAY . ']"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['overlay_section'][VvjlConstants::OPTION_OVERLAY_OPACITY] = [
      '#type' => 'range',
      '#title' => $this->t('Overlay Opacity'),
      '#default_value' => $this->options[VvjlConstants::OPTION_OVERLAY_OPACITY],
      '#min' => VvjlConstants::MIN_OPACITY,
      '#max' => VvjlConstants::MAX_OPACITY,
      '#step' => VvjlConstants::OPACITY_STEP,
      '#description' => $this->t('Set the opacity level for the overlay.'),
      '#suffix' => '<span id="overlay-opacity-value">' . $this->options[VvjlConstants::OPTION_OVERLAY_OPACITY] . '</span>',
      '#attributes' => [
        'oninput' => 'document.getElementById("overlay-opacity-value").innerText = this.value;',
      ],
      '#states' => [
        'disabled' => [
          ':input[name="style_options[overlay_section][' . VvjlConstants::OPTION_DISABLE_OVERLAY . ']"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['overlay_section'][VvjlConstants::OPTION_DISABLE_OVERLAY] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Overlay Color'),
      '#default_value' => $this->options[VvjlConstants::OPTION_DISABLE_OVERLAY],
      '#description' => $this->t('Check this to disable overlay color completely.'),
    ];
  }

  /**
   * Build token documentation section.
   */
  protected function buildTokenDocumentation(array &$form): void {
    $form['token_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Token Documentation'),
      '#open' => FALSE,
      '#weight' => 100,
    ];

    $form['token_section']['description'] = [
      '#markup' => $this->t('<p>When using <em>Global: Text area</em> or <em>Global: Unfiltered text</em> in the Views header, footer, or empty text areas, the default Twig-style tokens (e.g., <code>{{ title }}</code>) will not work with the VVJL style plugin.</p>
        <p>Instead, use the custom VVJL token format to access field values from the <strong>first row</strong> of the View result:</p>
        <ul>
          <li><code>[vvjl:field_name]</code> – The rendered output of the field (e.g., linked title, image, formatted text).</li>
          <li><code>[vvjl:field_name:plain]</code> – A plain-text version of the field, with all HTML stripped.</li>
        </ul>
        <p>Examples:</p>
        <ul>
          <li><code>{{ title }}</code> ➜ <code>[vvjl:title]</code></li>
          <li><code>{{ field_image }}</code> ➜ <code>[vvjl:field_image]</code></li>
          <li><code>{{ body }}</code> ➜ <code>[vvjl:body:plain]</code></li>
        </ul>
        <p>These tokens offer safe and flexible field output for dynamic headings, summaries, and fallback messages in VVJL-enabled Views.</p>'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state): void {
    $values = $form_state->getValue('style_options');
    $flattened_values = $this->flattenFormValues($values);
    $form_state->setValue('style_options', $flattened_values);

    parent::submitOptionsForm($form, $form_state);
  }

  /**
   * Flatten nested form values to match original structure.
   */
  protected function flattenFormValues(array $values): array {
    $flattened = [];

    if (isset($values['grid_section'])) {
      $flattened[VvjlConstants::OPTION_GRID_IMAGE_WIDTH] = $values['grid_section'][VvjlConstants::OPTION_GRID_IMAGE_WIDTH] ?? VvjlConstants::DEFAULT_GRID_IMAGE_WIDTH;
      $flattened[VvjlConstants::OPTION_GRID_IMAGE_GAP] = $values['grid_section'][VvjlConstants::OPTION_GRID_IMAGE_GAP] ?? VvjlConstants::DEFAULT_GRID_IMAGE_GAP;
    }

    if (isset($values['animation_section'])) {
      $flattened[VvjlConstants::OPTION_ANIMATION] = $values['animation_section'][VvjlConstants::OPTION_ANIMATION] ?? VvjlConstants::DEFAULT_ANIMATION;
    }

    if (isset($values['overlay_section'])) {
      $flattened[VvjlConstants::OPTION_OVERLAY_COLOR] = $values['overlay_section'][VvjlConstants::OPTION_OVERLAY_COLOR] ?? VvjlConstants::DEFAULT_OVERLAY_COLOR;
      $flattened[VvjlConstants::OPTION_OVERLAY_OPACITY] = $values['overlay_section'][VvjlConstants::OPTION_OVERLAY_OPACITY] ?? VvjlConstants::DEFAULT_OVERLAY_OPACITY;
      $flattened[VvjlConstants::OPTION_DISABLE_OVERLAY] = $values['overlay_section'][VvjlConstants::OPTION_DISABLE_OVERLAY] ?? VvjlConstants::DEFAULT_DISABLE_OVERLAY;
    }

    $flattened[VvjlConstants::OPTION_UNIQUE_ID] = $this->options[VvjlConstants::OPTION_UNIQUE_ID] ?? $this->generateUniqueId();

    return $flattened;
  }

  /**
   * Generates a unique numeric ID for the view display.
   *
   * @return int
   *   A unique ID between 10000000 and 99999999.
   *
   * @throws \Random\RandomException
   *   If an appropriate source of randomness cannot be found.
   */
  protected function generateUniqueId(): int {
    if ($this->cachedUniqueId !== NULL) {
      return $this->cachedUniqueId;
    }

    $this->cachedUniqueId = random_int(VvjlConstants::MIN_UNIQUE_ID, VvjlConstants::MAX_UNIQUE_ID);

    return $this->cachedUniqueId;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(): array {
    $errors = parent::validate();

    if (!$this->usesFields()) {
      $errors[] = $this->t('Views Lightbox requires Fields as row style');
    }

    return $errors;
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $rows = [];

    if (!empty($this->view->result)) {
      foreach ($this->view->result as $row) {
        $rendered_row = $this->view->rowPlugin->render($row);
        if ($rendered_row !== NULL) {
          $rows[] = $rendered_row;
        }
      }
    }

    $libraries = $this->buildLibraryList();

    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#rows' => $rows,
      '#unique_id' => $this->options[VvjlConstants::OPTION_UNIQUE_ID] ?? $this->generateUniqueId(),
      '#attached' => [
        'library' => $libraries,
      ],
    ];

    return $build;
  }

  /**
   * Build the list of libraries to attach.
   *
   * @return array
   *   An array of library names to attach.
   */
  protected function buildLibraryList(): array {
    return [VvjlConstants::LIBRARY_JS];
  }

}
