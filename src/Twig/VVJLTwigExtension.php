<?php

namespace Drupal\vvjl\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;

/**
 * Class VVJTTwigExtension.
 *
 * Provides a custom Twig extension for marking HTML content as safe.
 *
 * @package Drupal\vvjl\Twig
 */
class VVJLTwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('safe_html', $this->safeHtml(...), ['is_safe' => ['html']]),
    ];
  }

  /**
   * Marks the provided HTML string as safe.
   *
   * @param string $string
   *   The string to mark as safe.
   *
   * @return \Twig\Markup
   *   The safe HTML string.
   */
  public function safeHtml(string $string): Markup {
    return new Markup($string, 'UTF-8');
  }

}
