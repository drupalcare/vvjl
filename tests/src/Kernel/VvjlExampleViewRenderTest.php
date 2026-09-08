<?php

declare(strict_types=1);

namespace Drupal\Tests\vvjl\Kernel;

use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\vvj_core\Kernel\VvjArticleImageExampleKernelTrait;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\views\Views;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Renders the shipped example view all the way to HTML.
 *
 * WHY THIS EXISTS. Nothing in this module rendered its own example view. The
 * install test installs, the style plugin test reads options and inspects the
 * build array, and neither runs Twig. That is exactly the gap that let vvjp
 * ship HTTP 500 on its example page through the whole 2.0.0 release with a
 * fully green suite, and it let cloudflare_purge be certified Ready with a
 * fatal on every API error path.
 *
 * This module is NOT known to carry vvjp's defect — measured 2026-09-08, its
 * schema and its shipped view agree. The point is that nothing was watching:
 * a render is the cheapest check that goes red when a stored option becomes
 * something the template cannot print.
 *
 * There is deliberately NO breakpoint-shape guard here, unlike vvjh, vvjp and
 * vvjr: this module exposes no available_breakpoints style option at all. Its
 * schema declares none and its shipped view stores none, measured 2026-09-08.
 * A guard for an option that does not exist would pass forever and prove
 * nothing.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */
#[RunTestsInSeparateProcesses]
class VvjlExampleViewRenderTest extends KernelTestBase {

  use VvjArticleImageExampleKernelTrait;

  /**
   * {@inheritdoc}
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
   * The shipped example view renders to HTML without a render-array error.
   */
  public function testExampleViewRenders(): void {
    $this->createArticlesWithImages(2);

    $view = Views::getView('vvjl_example');
    $this->assertNotNull($view, 'The example view ships as optional config.');
    $view->setDisplay('page_1');

    $build = $view->preview('page_1');
    $this->assertIsArray($build, 'The view produced a render array.');

    $renderer = $this->container->get('renderer');
    $html = (string) $renderer->renderInIsolation($build);

    $this->assertNotEmpty($view->result, 'The view returned rows.');
    $this->assertStringContainsString('class="vvjl-inner"', $html, 'The wrapper rendered.');
    $this->assertStringContainsString('class="lightbox-modal"', $html, 'A row rendered.');
  }

  /**
   * Creates published articles carrying an image, so the view has rows.
   *
   * @param int $count
   *   How many nodes to create.
   */
  private function createArticlesWithImages(int $count): void {
    $file_system = $this->container->get('file_system');
    $directory = 'public://vvjl-render-test';
    $file_system->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    $uri = $file_system->copy(
      DRUPAL_ROOT . '/core/tests/fixtures/files/image-test.png',
      $directory . '/image-test.png',
      FileExists::Replace,
    );

    $file = File::create(['uri' => $uri, 'status' => 1]);
    $file->save();

    for ($i = 0; $i < $count; $i++) {
      Node::create([
        'type' => 'article',
        'title' => 'Render probe ' . $i,
        'status' => 1,
        'body' => ['value' => '<p>Body ' . $i . '</p>', 'format' => 'plain_text'],
        'field_image' => ['target_id' => $file->id(), 'alt' => 'Render probe'],
      ])->save();
    }
  }

}
