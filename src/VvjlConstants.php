<?php

declare(strict_types=1);

namespace Drupal\vvjl;

/**
 * Defines constants for the VVJL module.
 *
 * This class contains constants used across the module, primarily for
 * token processing, data attribute mapping, and Views integration.
 */
final class VvjlConstants {

  /**
   * Token namespace for VVJL tokens.
   */
  public const TOKEN_NAMESPACE = 'vvjl';

  /**
   * Plain text token suffix.
   */
  public const TOKEN_PLAIN_SUFFIX = ':plain';

  /**
   * Token pattern for validation.
   *
   * Validates token format: alphanumeric, underscores, optional :plain suffix.
   */
  public const TOKEN_PATTERN = '/^[a-zA-Z0-9_]+(:plain)?$/';

  /**
   * Configuration option keys.
   */
  public const OPTION_UNIQUE_ID = 'unique_id';
  public const OPTION_GRID_IMAGE_WIDTH = 'grid_image_width';
  public const OPTION_GRID_IMAGE_GAP = 'grid_image_gap';
  public const OPTION_OVERLAY_COLOR = 'overlay_color';
  public const OPTION_OVERLAY_OPACITY = 'overlay_opacity';
  public const OPTION_DISABLE_OVERLAY = 'disable_overlay';
  public const OPTION_ANIMATION = 'animation';

  /**
   * Default configuration values.
   */
  public const DEFAULT_GRID_IMAGE_WIDTH = 330;
  public const DEFAULT_GRID_IMAGE_GAP = 24;
  public const DEFAULT_OVERLAY_COLOR = '#ffffff';
  public const DEFAULT_OVERLAY_OPACITY = 0.7;
  public const DEFAULT_DISABLE_OVERLAY = FALSE;
  public const DEFAULT_ANIMATION = 'a-bottom';

  /**
   * Minimum values for validation.
   */
  public const MIN_GRID_IMAGE_WIDTH = 50;
  public const MIN_GRID_IMAGE_GAP = 0;
  public const MIN_OPACITY = 0;
  public const MIN_UNIQUE_ID = 10000000;

  /**
   * Maximum values for validation.
   */
  public const MAX_OPACITY = 1;
  public const MAX_UNIQUE_ID = 99999999;

  /**
   * Step values for form elements.
   */
  public const OPACITY_STEP = 0.1;

  /**
   * Data attribute mapping for lightbox options.
   *
   * Maps internal option keys to HTML data attribute names.
   */
  public const DATA_ATTRIBUTE_MAP = [
    'grid_image_width' => 'grid-image-width',
    'grid_image_gap' => 'grid-image-gap',
    'animation' => 'animation',
  ];

  /**
   * Boolean option mapping for lightbox controls.
   *
   * Maps internal boolean option keys to HTML data attribute names.
   */
  public const BOOLEAN_ATTRIBUTE_MAP = [
    'disable_overlay' => 'disable-overlay',
  ];

  /**
   * Views integration field type constants.
   */
  public const VIEWS_TYPE_INTEGER = 'integer';
  public const VIEWS_TYPE_STRING = 'string';
  public const VIEWS_TYPE_BOOLEAN = 'boolean';
  public const VIEWS_TYPE_NUMERIC = 'numeric';
  public const VIEWS_TYPE_FLOAT = 'float';

  /**
   * Plugin ID for the Views style plugin.
   */
  public const PLUGIN_ID = 'views_vvjl';

  /**
   * Theme hook for the main lightbox view.
   */
  public const THEME_HOOK = 'views_view_vvjl';

  /**
   * Theme hook for field rows.
   */
  public const THEME_HOOK_FIELDS = 'views_view_vvjl_fields';

  /**
   * CSS class for the lightbox wrapper.
   */
  public const CSS_CLASS_WRAPPER = 'vvj-lightbox';

  /**
   * Library name for core JavaScript.
   */
  public const LIBRARY_JS = 'vvjl/vvjl';

  /**
   * Example view ID.
   */
  public const EXAMPLE_VIEW_ID = 'vvjl_example';

  /**
   * Private constructor to prevent instantiation.
   *
   * This class should only be used for its constants.
   */
  private function __construct() {
  }

}
