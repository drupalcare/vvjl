<?php

declare(strict_types=1);

namespace Drupal\Tests\vvjl\FunctionalJavascript;

use Drupal\Tests\vvj_core\FunctionalJavascript\VvjPatternJavascriptTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Browser smoke tests for the vvjl example view custom element.
 */
#[Group('vvjl')]
#[Group('vvj_javascript')]
final class VvjlJavascriptSmokeTest extends VvjPatternJavascriptTestBase {

  /**
   * {@inheritdoc}
   *
   * @var list<string>
   */
  protected static $modules = [
    'node',
    'block',
    'views',
    'field',
    'file',
    'filter',
    'text',
    'image',
    'vvj_core',
    'vvjl',
  ];

  /**
   * {@inheritdoc}
   */
  protected function vvjExampleViewId(): string {
    return 'vvjl_example';
  }

  /**
   * {@inheritdoc}
   */
  protected function vvjCustomElementTag(): string {
    return 'vvjl-lightbox';
  }

  /**
   * {@inheritdoc}
   */
  protected function vvjRequiresArticleImageField(): bool {
    return TRUE;
  }

}
