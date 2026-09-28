<?php

declare(strict_types=1);

namespace Drupal\graphql_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\graphql_api\Service\GraphqlClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller.
 */
final class AutocompleteController extends ControllerBase {

  public function __construct(private readonly GraphqlClient $client) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('graphql_api.client'));
  }

  public function autocomplete(Request $request): JsonResponse {
    $input = $request->query->all()['q'] ?? '';
    $matches = [];
    if (is_string($input)) {
      $input = trim($input);
      if (mb_strlen($input) >= 1 && mb_strlen($input) <= 128) {
        try {
          $matches = $this->client->search($input);
          foreach ($matches as &$match) {
            $match['url'] = Url::fromRoute('graphql_api.certification', ['certification_id' => $match['id']])->toString();
          }
          unset($match);
        }
        catch (\Exception $exception) {
          // Do not log upstream bodies, tokens, or search terms.
          $this->getLogger('graphql_api')->warning('GraphQL autocomplete request failed (@type). The VAE certification service could not be queried.', [
            '@type' => get_class($exception),
          ]);
        }
      }
    }
    return new JsonResponse($matches, 200, ['Cache-Control' => 'private, no-store']);
  }

}
