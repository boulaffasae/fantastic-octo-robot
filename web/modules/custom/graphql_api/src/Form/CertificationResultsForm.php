<?php

declare(strict_types=1);

namespace Drupal\graphql_api\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Compact search form rendered directly on the results page.
 */
final class CertificationResultsForm extends GraphqlSearchForm {

  public function getFormId(): string {
    return 'graphql_api_certification_results_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, string $search = ''): array {
    $form = parent::buildForm($form, $form_state, $search);
    $form['search_bar']['#attributes']['class'] = ['fr-search-bar'];
    $form['search_bar']['submit']['#theme'] = 'input__graphql_search_submit';
    $form['search_bar']['submit']['#theme_wrappers'] = [];
    return $form;
  }

}
