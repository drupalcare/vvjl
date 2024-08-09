<?php

namespace Drupal\vvjl\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Core\Url;

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
   * Does the style plugin use a row plugin.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Set default options.
   */
  protected function defineOptions(): array {
    $options = parent::defineOptions();
    $options['unique_id'] = ['default' => $this->generateUniqueId()];
    $options['grid_image_width'] = ['default' => 300];
    $options['grid_image_gap'] = ['default' => 20];
    $options['overlay_color'] = ['default' => '#000000'];
    $options['overlay_opacity'] = ['default' => 0.4];
    $options['disable_overlay'] = ['default' => FALSE];
    $options['animation'] = ['default' => 'vvjl__animate_bottom'];
    return $options;
  }

  /**
   * Builds the options form for configuring the plugin.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Add a warning message if the first field is not set to "URL to image".
    $form['warning_message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--warning">' . $this->t('Note: The first field must be an image. To see an example, check the vvjl_example view by clicking <a href="/admin/structure/views/view/vvjl_example" style="display: inline;">here</a> to edit it.', ['@url' => Url::fromRoute('entity.view.edit_form', ['view' => 'vvjl_example'])->toString()]) . '</div>',
    ];

    $form['grid_image_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Grid Image Width (px)'),
      '#default_value' => $this->options['grid_image_width'],
      '#description' => $this->t('Set the width of the grid image in pixels.'),
      '#step' => 1,
      '#min' => 1,
    ];

    $form['grid_image_gap'] = [
      '#type' => 'number',
      '#title' => $this->t('Grid Image Gap (px)'),
      '#default_value' => $this->options['grid_image_gap'],
      '#description' => $this->t('Set the gap between grid images in pixels.'),
      '#step' => 1,
      '#min' => 1,
    ];

    $form['animation'] = [
      '#type' => 'select',
      '#title' => $this->t('Animation Type'),
      '#options' => [
        'none' => $this->t('None'),
        'vvjl__animate_top' => $this->t('Top'),
        'vvjl__animate_bottom' => $this->t('Bottom'),
        'vvjl__animate_left' => $this->t('Left'),
        'vvjl__animate_right' => $this->t('Right'),
        'vvjl__animate_zoom' => $this->t('Zoom'),
      ],
      '#default_value' => $this->options['animation'],
      '#description' => $this->t('Choose the animation type to apply when click next/prev in the modal.'),
    ];

    // Overlay color configuration.
    $form['overlay_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Overlay Color'),
      '#default_value' => $this->options['overlay_color'],
      '#description' => $this->t('Choose a color overlay for the lightbox background.'),
    ];

    // Overlay opacity configuration.
    $form['overlay_opacity'] = [
      '#type' => 'range',
      '#title' => $this->t('Overlay Opacity'),
      '#default_value' => $this->options['overlay_opacity'],
      '#min' => 0,
      '#max' => 1,
      '#step' => 0.1,
      '#description' => $this->t('Set the opacity level for the overlay.'),
      '#suffix' => '<span id="overlay-opacity-value">' . $this->options['overlay_opacity'] . '</span>',
      '#attributes' => [
        'oninput' => 'document.getElementById("overlay-opacity-value").innerText = this.value;',
      ],
    ];
    // Add the disable background checkbox.
    $form['disable_overlay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Overlay Color'),
      '#default_value' => $this->options['disable_overlay'],
      '#description' => $this->t('Check this to disable overlay color completely.'),
    ];
  }

  /**
   * Generates a unique numeric ID for the view display.
   *
   * @return int
   *   A unique numeric ID.
   *
   * @throws \Random\RandomException
   *   Thrown if an appropriate source of randomness cannot be found.
   */
  protected function generateUniqueId(): int {
    // 8 digit unique ID
    return random_int(10000000, 99999999);
  }

  /**
   * Renders the view with the lightbox style.
   *
   * @return array
   *   A render array for the lightbox view.
   */
  public function render(): array {
    $rows = [];
    foreach ($this->view->result as $row) {
      // Render the row content.
      $rows[] = $this->view->rowPlugin->render($row);
    }

    $libraries = [
      'vvjl/vvjl',
    ];

    return [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#rows' => $rows,
      '#unique_id' => $this->options['unique_id'],
      '#attached' => [
        'library' => $libraries,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $errors = parent::validate();
    if (!$this->usesFields()) {
      $errors[] = $this->t('Views Lightbox requires Fields as row style');
    }
    return $errors;
  }

}
