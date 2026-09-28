<?php

declare(strict_types=1);

namespace Drupal\graphql_api\Controller;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays the candidate page with its certification search block.
 */
final class CandidateController extends ControllerBase {

  public function __construct(private readonly BlockManagerInterface $blockManager) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.block'));
  }

  public function content(): array {
    $block = $this->blockManager->createInstance('graphql_api_search', [
      'label_display' => FALSE,
    ]);
    $access = $block->access($this->currentUser(), TRUE);
    $build = [
      '#theme' => 'block',
      '#configuration' => $block->getConfiguration(),
      '#plugin_id' => $block->getPluginId(),
      '#base_plugin_id' => $block->getBaseId(),
      '#derivative_plugin_id' => $block->getDerivativeId(),
      '#access' => $access,
      'content' => $access->isAllowed() ? $block->build() : [],
    ];
    CacheableMetadata::createFromObject($block)
      ->addCacheableDependency($access)
      ->applyTo($build);
    return $build;
  }

}
