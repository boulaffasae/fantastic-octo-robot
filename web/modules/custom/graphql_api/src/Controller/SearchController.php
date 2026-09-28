<?php

declare(strict_types=1);

namespace Drupal\graphql_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Url;
use Drupal\graphql_api\Form\CertificationResultsForm;
use Drupal\graphql_api\Service\GraphqlClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Displays certification search results, nine per page.
 */
final class SearchController extends ControllerBase {

  private const PAGE_SIZE = 50;

  public function __construct(
    private readonly GraphqlClient $client,
    private readonly PagerManagerInterface $pagerManager,
    private readonly RequestStack $requestStack,
  ) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('graphql_api.client'), $container->get('pager.manager'), $container->get('request_stack'));
  }

  public function title(Request $request): \Drupal\Core\StringTranslation\TranslatableMarkup {
    // Breadcrumb builders may resolve the title with a query-free request.
    $request = $this->requestStack->getMainRequest() ?? $request;
    $input = $request->query->all()['q'] ?? '';
    return $this->t('Résultats de recherche pour "@search"', ['@search' => is_string($input) ? trim($input) : '']);
  }

  public function search(Request $request): array {
    $input = $request->query->all()['q'] ?? '';
    $search = is_string($input) ? trim($input) : '';
    $build = [
      '#cache' => ['max-age' => 0, 'contexts' => ['url.query_args']],
      '#attached' => ['library' => ['graphql_api/search_results']],
      'form' => $this->formBuilder()->getForm(CertificationResultsForm::class, $search),
    ];
    if ($search === '' || mb_strlen($search) > 128) {
      $build['message'] = ['#markup' => $this->t('Saisissez entre 1 et 128 caractères pour rechercher une certification.')];
      return $build;
    }

    try {
      // Drupal pager URLs are zero-based; displayed page numbers start at one.
      $requested_page = max(0, min(238609294, $this->pagerManager->findPage()));
      $results = $this->client->searchPage($search, self::PAGE_SIZE, $requested_page * self::PAGE_SIZE);
      $pager = $this->pagerManager->createPager($results['total'], self::PAGE_SIZE);
      // The pager clamps out-of-range requests to the last available page.
      if ($results['total'] > 0 && $pager->getCurrentPage() !== $requested_page) {
        $results = $this->client->searchPage($search, self::PAGE_SIZE, $pager->getCurrentPage() * self::PAGE_SIZE);
      }
      $build['results'] = [
        '#theme' => 'graphql_api_results',
        '#heading' => $this->title($request),
        '#total' => $results['total'],
        '#certifications' => array_map(static fn(array $row): array => [
          'label' => $row['label'],
          'codeRncp' => preg_replace('/^RNCP\s*/i', '', $row['codeRncp']),
          'authority' => $row['certificationAuthorityStructure']['label'] ?? '',
          'url' => Url::fromRoute('graphql_api.certification', ['certification_id' => $row['id']])->toString(),
        ], $results['rows']),
        '#empty' => $this->t('Aucune certification trouvée pour « @search ».', ['@search' => $search]),
        '#pager' => $pager->getTotalPages() > 1 ? [
          '#type' => 'pager',
          '#theme' => 'pager__graphql_search',
          '#quantity' => 9,
          '#parameters' => ['q' => $search],
        ] : NULL,
      ];
    }
    catch (\Exception $exception) {
      $this->getLogger('graphql_api')->warning('Certification search failed (@type).', ['@type' => get_class($exception)]);
      $build['error'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Le service de recherche est temporairement indisponible. Veuillez réessayer.'),
        '#attributes' => ['role' => 'alert'],
      ];
    }
    return $build;
  }

}
