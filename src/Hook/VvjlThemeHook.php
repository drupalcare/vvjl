<?php

declare(strict_types=1);

namespace Drupal\vvjl\Hook;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theme hook for vvjl — declares views_view_vvjl + views_view_vvjl_fields.
 */
final class VvjlThemeHook {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   For resolving the module's templates path.
   */
  public function __construct(
    private readonly ModuleExtensionList $moduleExtensionList,
  ) {}

  /**
   * Implements hook_theme().
   *
   * @param array<string, mixed> $existing
   *   The existing theme registry entries.
   * @param string $type
   *   The extension type.
   * @param string $theme
   *   The active theme name.
   * @param string $path
   *   The theme or module path.
   *
   * @return array<string, array{
   *   variables?: array<string, mixed>,
   *   template: string,
   *   path: string,
   *   }>
   *   Theme registry entries.
   */
  #[Hook('theme')]
  public function theme(array $existing, string $type, string $theme, string $path): array {
    $template_path = $this->moduleExtensionList->getPath('vvjl') . '/templates';
    return [
      'views_view_vvjl_fields' => [
        'variables' => [
          'view' => NULL,
          'options' => [],
          'row' => NULL,
          'field_alias' => NULL,
          'attributes' => [],
          'title_attributes' => [],
          'content_attributes' => [],
          'title_prefix' => [],
          'title_suffix' => [],
          'fields' => [],
        ],
        'template' => 'views-view-vvjl-fields',
        'path' => $template_path,
      ],
      'views_view_vvjl' => [
        'variables' => [
          'view' => NULL,
          'rows' => [],
          'options' => [],
        ],
        'template' => 'views-view-vvjl',
        'path' => $template_path,
      ],
    ];
  }

}
