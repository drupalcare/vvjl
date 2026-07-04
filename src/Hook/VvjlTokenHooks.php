<?php

declare(strict_types=1);

namespace Drupal\vvjl\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\vvj_core\Service\TokenResolver;
use Drupal\vvjl\Plugin\views\style\Lightbox;

/**
 * Token hooks — exposes per-row field tokens for VVJL-enabled Views.
 *
 * Token forms:
 *   [vvjl:FIELD]        — rendered HTML
 *   [vvjl:FIELD:plain]  — plain text.
 *
 * Resolution logic lives in vvj_core.token_resolver.
 */
final class VvjlTokenHooks {

  use StringTranslationTrait;

  /**
   * Constructor.
   *
   * @param \Drupal\vvj_core\Service\TokenResolver|null $tokenResolver
   *   Shared token resolver. Nullable to keep the container compilable
   *   during the v1 → v2 upgrade window when vvj_core is on disk but
   *   not yet enabled. vvjl_update_10001 installs it; subsequent
   *   container rebuilds inject the real service.
   */
  public function __construct(
    private readonly ?TokenResolver $tokenResolver,
  ) {}

  /**
   * Implements hook_token_info().
   *
   * @return array<string, array<string, array<string, mixed>>>
   *   Token type + token definitions for the `vvjl` namespace.
   */
  #[Hook('token_info')]
  public function tokenInfo(): array {
    return [
      'types' => [
        'vvjl' => [
          'name' => $this->t('VVJL Lightbox'),
          'description' => $this->t('Tokens for accessing field values from the first row of a VVJL-enabled View.'),
          'needs-data' => 'view',
        ],
      ],
      'tokens' => [
        'vvjl' => [
          '*' => [
            'name' => $this->t('Field token'),
            'description' => $this->t('Use [vvjl:FIELD_NAME] for HTML output or [vvjl:FIELD_NAME:plain] for plain text.'),
          ],
        ],
      ],
    ];
  }

  /**
   * Implements hook_tokens().
   *
   * @param string $type
   *   Token type — only `vvjl` is handled.
   * @param array<string, string> $tokens
   *   Token name → original placeholder map.
   * @param array<string, mixed> $data
   *   Token data; expects `view` key with a ViewExecutable.
   * @param array<string, mixed> $options
   *   Token resolution options.
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
   *   For propagating cache metadata.
   *
   * @return array<string, string|\Drupal\Core\Render\Markup>
   *   Map of original placeholder → resolved value.
   */
  #[Hook('tokens')]
  public function tokens(
    string $type,
    array $tokens,
    array $data,
    array $options,
    BubbleableMetadata $bubbleable_metadata,
  ): array {
    if ($type !== 'vvjl' || $this->tokenResolver === NULL) {
      return [];
    }
    return $this->tokenResolver->resolve(
      'vvjl',
      $tokens,
      $data,
      $bubbleable_metadata,
      Lightbox::class,
    );
  }

}
