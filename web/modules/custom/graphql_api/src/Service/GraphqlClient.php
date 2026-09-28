<?php

declare(strict_types=1);

namespace Drupal\graphql_api\Service;

use Drupal\Component\Utility\Html;
use GuzzleHttp\ClientInterface;

/**
 * Reads certifications from the public France VAE GraphQL API.
 */
final class GraphqlClient {

  public function __construct(private readonly ClientInterface $httpClient) {}

  /**
   * Returns up to five autocomplete suggestions, including their identifiers.
   */
  public function search(string $search): array {
    return array_map(static function (array $row): array {
      $display = $row['label'] . ($row['codeRncp'] !== '' ? ' (RNCP ' . $row['codeRncp'] . ')' : '');
      return [
        'id' => $row['id'],
        'value' => $row['label'],
        'label' => Html::escape($display),
        'codeRncp' => $row['codeRncp'],
      ];
    }, $this->searchPage($search, 5)['rows']);
  }

  /**
   * Fetches one page and the total count, without loading the other pages.
   */
  public function searchPage(string $search, int $limit = 9, int $offset = 0): array {
    $search = trim($search);
    if ($search === '' || mb_strlen($search) > 128) {
      return ['rows' => [], 'total' => 0];
    }
    if ($limit < 1 || $limit > 50 || $offset < 0 || $offset > 2147483647) {
      throw new \InvalidArgumentException('Invalid pagination parameters.');
    }
    $query = <<<'GRAPHQL'
      query searchCertificationsQuery($searchText: String!, $limit: Int!, $offset: Int!) {
        searchCertificationsForCandidate(searchText: $searchText, limit: $limit, offset: $offset) {
          rows { id label codeRncp certificationAuthorityStructure { label } }
          info { totalRows }
        }
      }
      GRAPHQL;
    $data = $this->request($query, ['searchText' => $search, 'limit' => $limit, 'offset' => $offset], 'searchCertificationsQuery');
    $page = $data['searchCertificationsForCandidate'] ?? NULL;
    if (!is_array($page) || !is_array($page['rows'] ?? NULL) || !array_is_list($page['rows']) || !is_int($page['info']['totalRows'] ?? NULL) || $page['info']['totalRows'] < 0) {
      throw new \RuntimeException('Invalid GraphQL search page.');
    }
    foreach ($page['rows'] as $row) {
      $this->validateCertification($row);
    }
    return ['rows' => $page['rows'], 'total' => $page['info']['totalRows']];
  }

  /**
   * Fetches the public information for a certification; NULL means not found.
   */
  public function getCertification(string $id): ?array {
    $query = <<<'GRAPHQL'
      query getCertificationForCertificationPage($certificationId: ID!) {
        getCertification(certificationId: $certificationId) {
          id label codeRncp level typeDiplome rncpObjectifsContexte rncpExpiresAt
          juryTypeMiseEnSituationProfessionnelle juryTypeSoutenanceOrale juryEstimatedCost juryPlace
          additionalInfo {
            dossierDeValidationLink
            dossierDeValidationTemplate { url name mimeType }
            linkToReferential linkToJuryGuide linkToCorrespondenceTable
            additionalDocuments { url name mimeType }
            certificationExpertContactDetails certificationExpertContactPhone certificationExpertContactEmail
            usefulResources commentsForAAP
          }
          certificationAuthorityStructure { label }
          prerequisites { label }
          competenceBlocs { code label competences { label } }
        }
      }
      GRAPHQL;
    $data = $this->request($query, ['certificationId' => $id], 'getCertificationForCertificationPage', TRUE);
    $certification = $data['getCertification'] ?? NULL;
    if ($certification !== NULL) {
      $this->validateCertification($certification);
    }
    return $certification;
  }

  private function validateCertification(mixed $row): void {
    if (!is_array($row) || !is_string($row['id'] ?? NULL) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $row['id']) || !is_string($row['label'] ?? NULL) || !is_string($row['codeRncp'] ?? NULL)) {
      throw new \RuntimeException('Invalid GraphQL certification.');
    }
  }

  private function request(string $query, array $variables, string $operation, bool $allowNotFound = FALSE): array {
    $response = $this->httpClient->request('POST', 'https://vae.gouv.fr/api/graphql', [
      'headers' => ['Accept' => 'application/json'],
      'json' => ['query' => $query, 'variables' => $variables, 'operationName' => $operation],
      'connect_timeout' => 3,
      'timeout' => 8,
      'allow_redirects' => FALSE,
    ]);
    if ($response->getStatusCode() !== 200) {
      throw new \RuntimeException('Unexpected GraphQL HTTP status.');
    }
    $payload = json_decode((string) $response->getBody(), TRUE, 512, JSON_THROW_ON_ERROR);
    // The public API declares this field non-nullable, even for unknown IDs.
    $error = is_array($payload) ? ($payload['errors'][0] ?? []) : [];
    if ($allowNotFound && count($payload['errors'] ?? []) === 1 && (
      ($error['extensions']['code'] ?? '') === 'NOT_FOUND'
      || (($error['path'] ?? []) === ['getCertification'] && ($error['message'] ?? '') === 'Cannot return null for non-nullable field Query.getCertification.')
    )) {
      return ['getCertification' => NULL];
    }
    if (!is_array($payload) || !empty($payload['errors']) || !is_array($payload['data'] ?? NULL)) {
      throw new \RuntimeException('Invalid GraphQL response.');
    }
    return $payload['data'];
  }

}
