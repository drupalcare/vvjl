<?php

/**
 * @file
 * Contains the hook implementations for the custom Views style plugin.
 */

/**
 * Implements hook_views_data_alter().
 */
function vvjl_views_data_alter(array &$data): void {
  // Check if the views_style_plugin table is defined in Views.
  if (!isset($data['views_style_plugin'])) {
    $data['views_style_plugin'] = [];
  }

  $data['views_style_plugin']['views_vvjl'] = [
    'group' => t('Style plugins'),
    'title' => t('Views Vanilla JavaScript Lightbox'),
    'help' => t('Render items in Lightbox using vanilla JavaScript.'),
    'style' => [
      'unique_id' => [
        'title' => t('Unique ID'),
        'help' => t('Unique identifier for the view display.'),
        'field' => [
          'id' => 'numeric',
        ],
      ],
      'grid_image_width' => [
        'title' => t('Grid Image Width (px)'),
        'help' => t('Set the width of the grid image in pixels.'),
        'field' => [
          'id' => 'numeric',
        ],
      ],
      'grid_image_gap' => [
        'title' => t('Grid Image Gap (px)'),
        'help' => t('Set the gap between grid images in pixels.'),
        'field' => [
          'id' => 'numeric',
        ],
      ],
      'overlay_color' => [
        'title' => t('Overlay Color'),
        'help' => t('Select the overlay color for the lightbox background.'),
        'field' => [
          'id' => 'string',
        ],
      ],
      'overlay_opacity' => [
        'title' => t('Overlay Opacity'),
        'help' => t('Set the opacity level for the overlay.'),
        'field' => [
          'id' => 'numeric',
        ],
      ],
      'disable_overlay' => [
        'title' => t('Disable Overlay Color'),
        'help' => t('Enable or disable the overlay color completely.'),
        'field' => [
          'id' => 'boolean',
        ],
      ],
      'animation' => [
        'type' => 'string',
        'label' => t('Animation Type'),
      ],
    ],
  ];
}
