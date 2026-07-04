<?php

declare(strict_types=1);

namespace Drupal\vvjl\Hook;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\filter\FilterPluginManager;

/**
 * Help hook for vvjl — renders README.md on the help page.
 */
final class VvjlHelpHook {

  use StringTranslationTrait;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   For resolving the module path.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   For checking whether the markdown filter module is enabled.
   * @param \Drupal\filter\FilterPluginManager $filterManager
   *   For instantiating the markdown filter when available.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   For loading markdown.settings when the filter is enabled.
   */
  public function __construct(
    private readonly ModuleExtensionList $moduleExtensionList,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly FilterPluginManager $filterManager,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Implements hook_help() for the vvjl help route.
   */
  #[Hook('help')]
  public function help(string $route_name, RouteMatchInterface $route_match): ?string {
    if ($route_name !== 'help.page.vvjl') {
      return NULL;
    }
    $module_path = $this->moduleExtensionList->getPath('vvjl');
    $text = @file_get_contents($module_path . '/README.md');
    if ($text === FALSE) {
      return (string) $this->t('README.md file not found.');
    }
    if (!$this->moduleHandler->moduleExists('markdown')) {
      return '<pre>' . Html::escape($text) . '</pre>';
    }
    $settings = $this->configFactory->get('markdown.settings')->getRawData();
    /** @var \Drupal\filter\Plugin\FilterInterface $filter */
    $filter = $this->filterManager->createInstance('markdown', ['settings' => $settings]);
    return $filter->process($text, 'en')->getProcessedText();
  }

}
