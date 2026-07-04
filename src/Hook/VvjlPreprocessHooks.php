<?php

declare(strict_types=1);

namespace Drupal\vvjl\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Template\Attribute;
use Drupal\views\Hook\ViewsThemeHooks;
use Drupal\views\ViewExecutable;
use Drupal\vvjl\Plugin\views\style\Lightbox;

/**
 * Preprocess hooks for VVJL lightbox.
 *
 * Three responsibilities:
 *   1. `views_view_vvjl` preprocess — flatten options, build the
 *      rgba background string for the overlay, rewrite per-row theme
 *      suggestions, delegate to Views unformatted preprocess.
 *   2. `views_view_vvjl_fields` preprocess — delegate to Views field
 *      preprocess.
 *   3. `views_view` preprocess — add legacy `vvj-lightbox` class for
 *      v1 contract preservation (vvj_core's preprocess adds the
 *      universal `vvj-component` + `vvj-vvjl` classes).
 */
final class VvjlPreprocessHooks {

  /**
   * Constructor.
   *
   * @param \Drupal\views\Hook\ViewsThemeHooks $viewsThemeHooks
   *   Views row / field preprocess (replaces removed procedural
   *   template_preprocess_* in Drupal 12).
   */
  public function __construct(
    private readonly ViewsThemeHooks $viewsThemeHooks,
  ) {}

  /**
   * Implements hook_preprocess_HOOK() for views_view_vvjl.
   *
   * @param array<string, mixed> $variables
   *   Template variables.
   */
  #[Hook('preprocess_views_view_vvjl')]
  public function preprocessViewsViewVvjl(array &$variables): void {
    $view = $variables['view'] ?? NULL;
    if (!($view instanceof ViewExecutable)) {
      return;
    }
    $stylePlugin = $view->style_plugin;
    if (!($stylePlugin instanceof Lightbox)) {
      return;
    }
    /** @var \Drupal\vvjl\Plugin\views\style\Lightbox $handler */
    $handler = $stylePlugin;
    $options = $handler->options;

    $list_attributes = [];
    if (!empty($options['grid_image_width'])) {
      $list_attributes['data-grid-image-width'] = $options['grid_image_width'];
    }
    if (!empty($options['grid_image_gap'])) {
      $list_attributes['data-grid-image-gap'] = $options['grid_image_gap'];
    }
    if (!empty($options['disable_overlay'])) {
      $list_attributes['data-disable-overlay'] = 'true';
    }
    if (!empty($options['animation'])) {
      $list_attributes['data-animation'] = $options['animation'];
    }

    $overlay_color = $options['overlay_color'] ?? '';
    if (is_string($overlay_color) && $overlay_color !== '') {
      $rgb = self::hexToRgb($overlay_color);
      $raw_opacity = $options['overlay_opacity'] ?? 1;
      $opacity = is_numeric($raw_opacity) ? (float) $raw_opacity : 1.0;
      $variables['background_rgb'] = sprintf(
        'rgba(%d, %d, %d, %s)',
        $rgb['r'],
        $rgb['g'],
        $rgb['b'],
        $opacity,
      );
    }
    else {
      $variables['background_rgb'] = NULL;
    }

    $variables['list_attributes'] = new Attribute($list_attributes);
    $variables['options'] = $options;
    $variables['settings'] = [
      'view_id' => $view->dom_id,
      'grid_image_width' => $options['grid_image_width'] ?? 0,
      'grid_image_gap' => $options['grid_image_gap'] ?? 0,
      'overlay_color' => $options['overlay_color'] ?? '',
      'overlay_opacity' => $options['overlay_opacity'] ?? 0,
    ];

    $rows = $variables['rows'] ?? NULL;
    if (is_array($rows)) {
      foreach ($rows as $key => $row) {
        if (!is_array($row) || !isset($row['#theme']) || !is_array($row['#theme'])) {
          continue;
        }
        foreach ($row['#theme'] as $idx => $hook) {
          if (is_string($hook)) {
            $row['#theme'][$idx] = str_replace(
              'views_view_fields',
              'views_view_vvjl_fields',
              $hook,
            );
          }
        }
        $rows[$key] = $row;
      }
      $variables['rows'] = $rows;
    }

    $this->viewsThemeHooks->preprocessViewsViewUnformatted($variables);
  }

  /**
   * Implements hook_preprocess_HOOK() for views_view_vvjl_fields.
   *
   * @param array<string, mixed> $variables
   *   Template variables.
   */
  #[Hook('preprocess_views_view_vvjl_fields')]
  public function preprocessViewsViewVvjlFields(array &$variables): void {
    $this->viewsThemeHooks->preprocessViewsViewFields($variables);
  }

  /**
   * Implements hook_preprocess_HOOK() for views_view.
   *
   * Adds the legacy `vvj-lightbox` class for v1 contract preservation.
   *
   * @param array<string, mixed> $variables
   *   Template variables.
   */
  #[Hook('preprocess_views_view')]
  public function preprocessViewsView(array &$variables): void {
    $view = $variables['view'] ?? NULL;
    if (!($view instanceof ViewExecutable)) {
      return;
    }
    if (!($view->style_plugin instanceof Lightbox)) {
      return;
    }
    $attributes = $variables['attributes'] ?? NULL;
    if (is_array($attributes)) {
      $classes = $attributes['class'] ?? [];
      if (!is_array($classes)) {
        $classes = [];
      }
      $classes[] = 'vvj-lightbox';
      $attributes['class'] = $classes;
      $variables['attributes'] = $attributes;
    }
  }

  /**
   * Convert hex color to RGB triplet.
   *
   * @param string $hex
   *   Hex color, with or without leading #.
   *
   * @return array{r: int, g: int, b: int}
   *   RGB components as integers in 0-255.
   */
  private static function hexToRgb(string $hex): array {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
      $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    return [
      'r' => (int) hexdec(substr($hex, 0, 2)),
      'g' => (int) hexdec(substr($hex, 2, 2)),
      'b' => (int) hexdec(substr($hex, 4, 2)),
    ];
  }

}
