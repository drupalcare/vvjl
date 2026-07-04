<?php

declare(strict_types=1);

namespace Drupal\Tests\vvjl\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\vvj_core\Kernel\VvjArticleImageExampleKernelTrait;
use Drupal\views\Views;
use Drupal\vvjl\Plugin\views\style\Lightbox;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies the views_vvjl style plugin loads and provides expected defaults.
 */
#[Group('vvjl')]
#[RunTestsInSeparateProcesses]
final class VvjlStylePluginTest extends KernelTestBase {

  use VvjArticleImageExampleKernelTrait;

  /**
   * {@inheritdoc}
   *
   * @var list<string>
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'file',
    'filter',
    'text',
    'image',
    'node',
    'views',
    'vvj_core',
    'vvjl',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->vvjKernelInstallArticleImageExampleEnvironment('vvjl');
  }

  /**
   * The style plugin is registered with the expected class and theme.
   */
  public function testStylePluginIsRegistered(): void {
    /** @var \Drupal\views\Plugin\ViewsPluginManager $manager */
    $manager = $this->container->get('plugin.manager.views.style');
    $definition = $manager->getDefinition('views_vvjl');
    $this->assertIsArray($definition);

    $this->assertSame(Lightbox::class, $definition['class']);
    $this->assertSame('views_view_vvjl', $definition['theme']);
  }

  /**
   * The example view loads and initializes the style plugin.
   */
  public function testExampleViewLoadsWithStyle(): void {
    $view = Views::getView('vvjl_example');
    $this->assertNotNull($view, 'vvjl_example view ships as optional config.');

    $view->setDisplay('default');
    $view->initStyle();

    $this->assertInstanceOf(Lightbox::class, $view->style_plugin);
    $this->assertSame('views_vvjl', $view->style_plugin->getPluginId());
  }

  /**
   * The style plugin defines the expected option defaults.
   */
  public function testStyleDefinesExpectedOptionDefaults(): void {
    $view = Views::getView('vvjl_example');
    $this->assertNotNull($view);
    $view->setDisplay('default');
    $view->initStyle();

    $options = $view->style_plugin->options;

    $this->assertArrayHasKey('grid_image_width', $options);
    $this->assertArrayHasKey('grid_image_gap', $options);
    $this->assertArrayHasKey('animation', $options);
    $this->assertArrayHasKey('unique_id', $options);

    // Lightbox intentionally disables deep linking (modal state is
    // transient and v1 vvjl shipped no deeplink feature), so these keys
    // must NOT be present.
    $this->assertArrayNotHasKey('enable_deeplink', $options);
    $this->assertArrayNotHasKey('deeplink_identifier', $options);
  }

  /**
   * Rendering produces cache metadata and attaches the pattern library.
   */
  public function testRenderProducesCacheableOutput(): void {
    $view = Views::getView('vvjl_example');
    $this->assertNotNull($view);
    $view->setDisplay('default');
    $view->execute();

    $build = $view->style_plugin->render();
    $this->assertIsArray($build);

    $this->assertArrayHasKey('#cache', $build);
    $cache = $build['#cache'];
    $this->assertIsArray($cache);
    $this->assertArrayHasKey('tags', $cache);
    $this->assertArrayHasKey('contexts', $cache);
    $this->assertArrayHasKey('max-age', $cache);

    $this->assertArrayHasKey('#attached', $build);
    $attached = $build['#attached'];
    $this->assertIsArray($attached);
    $this->assertArrayHasKey('library', $attached);
    $library = $attached['library'];
    $this->assertIsArray($library);
    $this->assertContains('vvjl/vvjl', $library);
  }

}
