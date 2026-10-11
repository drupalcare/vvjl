<?php

declare(strict_types=1);

namespace Drupal\Tests\vvjl\Kernel;

use Drupal\Component\Utility\Html;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\vvj_core\Kernel\VvjArticleImageExampleKernelTrait;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\views\Views;
use PHPUnit\Framework\Attributes\Group;
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
#[Group('vvjl')]
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
   * Every lightbox control button carries the `control-btn` class.
   *
   * A theme that styles every `<button>` (or `.button`) globally repaints the
   * open, close, previous and next controls. `control-btn` is the agreed
   * opt-out hook (owner decision 2026-10-07): such a theme excludes
   * `.control-btn`, so the controls keep this module's own design.
   */
  public function testControlButtonsCarryControlBtn(): void {
    $this->createArticlesWithImages(2);

    $view = Views::getView('vvjl_example');
    $this->assertNotNull($view);
    $view->setDisplay('page_1');
    $build = $view->preview('page_1');
    $this->assertIsArray($build);
    $html = (string) $this->container->get('renderer')->renderInIsolation($build);
    $xpath = new \DOMXPath(Html::load($html));

    // Each control is located by its own class first, so an empty match fails
    // as "not rendered" rather than passing as "nothing lacks the class".
    foreach (['modal-close', 'modal-prev', 'modal-next', 'modal-open'] as $class) {
      $nodes = $xpath->query('//button[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]');
      $this->assertNotFalse($nodes);
      $this->assertGreaterThan(0, $nodes->length, "The $class control rendered.");
      foreach ($nodes as $node) {
        assert($node instanceof \DOMElement);
        $classes = preg_split('/\s+/', trim($node->getAttribute('class'))) ?: [];
        $this->assertContains('control-btn', $classes, "The $class control carries control-btn.");
        $this->assertContains($class, $classes, "The $class control keeps its own class.");
      }
    }

    // Every button in this template is a control, so a new button added
    // without the class goes red here too.
    $buttons = $xpath->query('//button');
    $this->assertNotFalse($buttons);
    foreach ($buttons as $button) {
      assert($button instanceof \DOMElement);
      $classes = preg_split('/\s+/', trim($button->getAttribute('class'))) ?: [];
      $this->assertContains('control-btn', $classes, 'Button "' . $button->getAttribute('class') . '" carries control-btn.');
    }
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
