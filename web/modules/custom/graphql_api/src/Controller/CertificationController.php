<?php

declare(strict_types=1);

namespace Drupal\graphql_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\graphql_api\Service\GraphqlClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Displays the public details of a certification.
 */
final class CertificationController extends ControllerBase {

  public function __construct(private readonly GraphqlClient $client) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('graphql_api.client'));
  }

  public function content(string $certification_id): array {
    try {
      $certification = $this->client->getCertification($certification_id);
    }
    catch (\Exception $exception) {
      $this->getLogger('graphql_api')->warning('Certification details failed (@type).', ['@type' => get_class($exception)]);
      throw new ServiceUnavailableHttpException(60, (string) $this->t('La fiche est temporairement indisponible. Veuillez réessayer.'));
    }
    if ($certification === NULL) {
      throw new NotFoundHttpException((string) $this->t('Certification introuvable.'));
    }
    $code = preg_replace('/^RNCP\s*/i', '', $certification['codeRncp']);
    // Only HTTP(S) document links from the remote service may become anchors.
    $info = $certification['additionalInfo'] ?? [];
    foreach (['dossierDeValidationLink', 'linkToReferential', 'linkToJuryGuide', 'linkToCorrespondenceTable'] as $field) {
      $info[$field] = $this->safeUrl($info[$field] ?? NULL);
    }
    if (!empty($info['dossierDeValidationTemplate'])) {
      $info['dossierDeValidationTemplate']['url'] = $this->safeUrl($info['dossierDeValidationTemplate']['url'] ?? NULL);
    }
    foreach ($info['additionalDocuments'] ?? [] as $index => $document) {
      $info['additionalDocuments'][$index]['url'] = $this->safeUrl($document['url'] ?? NULL);
    }
    $certification['additionalInfo'] = $info;
    // The API Date scalar is a Unix timestamp in milliseconds.
    $expires = $certification['rncpExpiresAt'] ?? NULL;
    $expiration = is_numeric($expires) ? gmdate('d/m/Y', (int) ((float) $expires / 1000)) : '';
    return [
      '#theme' => 'graphql_api_certification',
      '#title' => $certification['label'],
      '#certification' => $certification,
      '#code' => $code,
      '#expiration' => $expiration,
      // Render arrays keep Drupal's XSS filtering for upstream rich text.
      '#summary' => ['#markup' => $certification['rncpObjectifsContexte'] ?? ''],
      '#rncp_url' => 'https://www.francecompetences.fr/recherche/rncp/' . rawurlencode($code) . '/',
      '#back_url' => Url::fromRoute('graphql_api.page')->toString(),
      '#attached' => ['library' => ['graphql_api/certification']],
      // Document download URLs are signed and short-lived.
      '#cache' => ['max-age' => 0],
    ];
  }

  private function safeUrl(?string $url): string {
    return $url !== NULL && filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], TRUE) ? $url : '';
  }

}
