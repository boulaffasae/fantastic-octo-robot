<?php

declare(strict_types=1);

namespace Drupal\graphql_api\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql_api\Form\GraphqlSearchForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the certification search introduction and form.
 */
#[Block(
  id: 'graphql_api_search',
  admin_label: new TranslatableMarkup('Certification search'),
)]
final class GraphqlSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, private readonly FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('form_builder'));
  }

  public function build(): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['s-cert']],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Recherchez le diplôme qui vous correspond'),
        '#attributes' => ['class' => ['s-cert__title']],
      ],
      'subtitle' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Indiquez ci-dessous la certification ou le diplôme souhaité :'),
        '#attributes' => ['class' => ['s-cert__subtitle']],
      ],
      'form' => $this->formBuilder->getForm(GraphqlSearchForm::class),
    ];
  }

}
