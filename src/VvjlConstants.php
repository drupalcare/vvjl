<?php

declare(strict_types=1);

namespace Drupal\vvjl;

/**
 * Vvjl-specific constants — lightbox's pattern-only values.
 *
 * Cross-cutting constants (token pattern, transition-speed bounds,
 * unique-ID range) live in `Drupal\vvj_core\Constants\ValidationBounds`.
 *
 * @internal Final, non-instantiable.
 */
final class VvjlConstants {

  /**
   * Animation type values (lightbox-specific).
   */
  public const string ANIMATION_NONE = 'none';
  public const string ANIMATION_ZOOM = 'a-zoom';
  public const string ANIMATION_TOP = 'a-top';
  public const string ANIMATION_RIGHT = 'a-right';
  public const string ANIMATION_BOTTOM = 'a-bottom';
  public const string ANIMATION_LEFT = 'a-left';

  /**
   * Default animation (slide-from-bottom).
   */
  public const string DEFAULT_ANIMATION = self::ANIMATION_BOTTOM;

  /**
   * Grid + overlay defaults.
   */
  public const int DEFAULT_GRID_IMAGE_WIDTH = 330;
  public const int DEFAULT_GRID_IMAGE_GAP = 24;
  public const string DEFAULT_OVERLAY_COLOR = '#ffffff';
  public const float DEFAULT_OVERLAY_OPACITY = 0.7;
  public const bool DEFAULT_DISABLE_OVERLAY = FALSE;

  /**
   * Validation bounds.
   */
  public const int MIN_GRID_IMAGE_WIDTH = 50;
  public const int MIN_GRID_IMAGE_GAP = 0;
  public const float MIN_OPACITY = 0.0;
  public const float MAX_OPACITY = 1.0;
  public const float OPACITY_STEP = 0.1;

  /**
   * Prevent instantiation.
   */
  private function __construct() {
  }

}
