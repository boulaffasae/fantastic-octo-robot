<?php

/**
 * Live smoke checks. Run with:
 * ddev exec vendor/bin/drush php:script web/modules/custom/graphql_api/tests/live-check.php
 */

$check = static function (bool $condition, string $message): void {
  if (!$condition) {
    throw new \RuntimeException($message);
  }
  echo "PASS: $message\n";
};
$client = \Drupal::service('graphql_api.client');
$first = $client->searchPage('a', 9, 0);
$second = $client->searchPage('a', 9, 9);
$check(count($first['rows']) === 9 && count($second['rows']) === 9, 'One-character search returns nine results per page');
$check(!array_intersect(array_column($first['rows'], 'id'), array_column($second['rows'], 'id')), 'Successive pages contain different certifications');
$cert = $client->getCertification('4371086c-0ee3-4b64-969e-3698d5ad905f');
$check(!empty($cert['label']), 'Example certification loads');
$check($client->getCertification('00000000-0000-0000-0000-000000000000') === NULL, 'Missing certification returns NULL');
$check($client->searchPage('   ') === ['rows' => [], 'total' => 0], 'Blank search is empty');
$http = \Drupal::httpClient();
$get = static fn(string $path) => $http->get('http://localhost' . $path, ['http_errors' => FALSE, 'timeout' => 30]);
$response = $get('/api/graphql?q=a');
$items = json_decode((string) $response->getBody(), TRUE);
$check($response->getStatusCode() === 200 && count($items) === 5 && str_starts_with($items[0]['url'], '/certifications/'), 'Autocomplete exposes five local certification links');
foreach (['0', '1', '999999'] as $page) {
  $response = $get('/espace-candidat/recherche?q=a&page=' . $page);
  $html = (string) $response->getBody();
  $check($response->getStatusCode() === 200 && substr_count($html, 'href="/certifications/') > 0 && str_contains($html, 'q=a'), 'Search page ' . $page . ' renders results and preserves the query');
  if ($page === '1') {
    $check(substr_count($html, 'href="/certifications/') === 9, 'Rendered second page contains exactly nine certification links');
    $check(str_contains($html, 'fr-pagination__link--first') && str_contains($html, 'fr-pagination__link--prev') && str_contains($html, 'fr-pagination__link--next') && str_contains($html, 'fr-pagination__link--last'), 'Pager contains first, previous, next and last controls');
  }
}
$check($get('/certifications/4371086c-0ee3-4b64-969e-3698d5ad905f/')->getStatusCode() === 200, 'Certification route renders');
$check($get('/certifications/00000000-0000-0000-0000-000000000000/')->getStatusCode() === 404, 'Unknown certification route returns 404');
$check($get('/certifications/invalid/')->getStatusCode() === 404, 'Invalid certification identifier returns 404');
$response = $get('/espace-candidat/recherche?q=zzzzzzzzzzzzzzzzzzzz');
$check($response->getStatusCode() === 200 && str_contains((string) $response->getBody(), 'Aucune certification'), 'No-results message renders');
