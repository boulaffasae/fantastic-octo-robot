<?php

declare(strict_types=1);

namespace Drupal\graphql_api\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Shows a search form.
 */
class GraphqlSearchForm extends FormBase {

  public function getFormId(): string {
    return 'graphql_api_search_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, string $search = ''): array {
    $search_id = Html::getUniqueId('graphql-api-search');
    $form['#attached']['library'][] = 'graphql_api/autocomplete';
    $form['search_bar'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['fr-search-bar', 'fr-search-bar--lg'],
        'role' => 'search',
        'aria-label' => $this->t('Rechercher une certification'),
      ],
    ];
    $form['search_bar']['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#value' => $this->t('Rechercher'),
      '#attributes' => ['class' => ['fr-label'], 'for' => $search_id],
    ];
    $form['search_bar']['search'] = [
      '#type' => 'textfield',
      '#id' => $search_id,
      '#title' => $this->t('Rechercher'),
      '#title_display' => 'invisible',
      '#theme_wrappers' => [],
      '#attributes' => [
        'class' => ['fr-input', 's-cert__input'],
        'data-autocomplete-results-url' => Url::fromUserInput('/espace-candidat/recherche/')->toString(),
      ],
      '#placeholder' => 'Ex : bac, cap, master, titre professionnel, code RNCP...',
      '#autocomplete_route_name' => 'graphql_api.autocomplete',
      '#default_value' => $search,
      '#maxlength' => 128,
      '#required' => TRUE,
    ];
    $form['search_bar']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Rechercher'),
      '#attributes' => ['class' => ['fr-btn']],
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $form_state->setRedirectUrl(Url::fromUserInput('/espace-candidat/recherche/', [
      'query' => ['q' => $form_state->getValue('search')],
    ]));
  }

}
