# GraphQL API

Enable with `ddev drush en graphql_api -y`, then `ddev drush cr`.

- Candidate page and search form: `/espace-candidat`.
- Autocomplete endpoint: `/api/graphql?q=a` (up to five suggestions).
- Search: `/espace-candidat/recherche?q=a` (nine results per page).
- Certification: `/certifications/4371086c-0ee3-4b64-969e-3698d5ad905f/`.

The candidate page uses `CandidateController` to render the `graphql_api_search`
block (`GraphqlSearchBlock`), containing the heading, introduction, and
`GraphqlSearchForm`. It can also be placed through Drupal's block layout.

Searches accept 1–128 characters. Suggestions include a certification ID and a
local route URL. Clicking or selecting a suggestion with the keyboard opens its
certification page. “Voir tous les résultats” and form submission open the search
page with the `q` query parameter.

`SearchController` requests nine rows and the total from France VAE. The Drupal
pager provides numbered pages, first/previous/next/last links, and preserves `q`.
Drupal uses zero-based URL page indexes: `?q=a&page=1` is the second page.
Out-of-range pages are clamped to the last available page. Empty queries do not
call the upstream service. Search failures show a retry message.

`CertificationController` loads the selected certification and displays its
label, RNCP code, level, diploma type, certificateur and expiration date.
The reusable `graphql-api-certification.html.twig` template follows the France
VAE content layout: DSFR tabs for Métier, Blocs de compétences (DSFR accordions),
Prérequis, Jury and Documentation, followed by resource cards. Jury modalities,
fees, locations and document links come from the API. The DSFR theme supplies
component styles and behavior; `css/certification.css` only adds responsive
layout adjustments, with no Tailwind dependency. It links to the RNCP record. Unknown IDs
return 404; upstream failures return 503. Upstream rich text uses Drupal's default
safe HTML filtering; other API text is escaped. These live pages are not cached.

## Public upstream GraphQL API

Endpoint: https://vae.gouv.fr/api/graphql (POST JSON).
Production introspection (`__schema` / `__type`) is disabled, as verified against
the live endpoint. GraphQL exposes named queries through this one endpoint,
rather than one REST URL per operation. Definitions can be inspected in the
[public France VAE schema](https://github.com/betagouv/reva/blob/master/packages/reva-api/modules/referential/referential.graphql).
Schema presence alone does not imply anonymous access to every operation.

Operations used here and verified without authentication:

- `searchCertificationsForCandidate(searchText: String, limit: Int, offset: Int)`:
  `rows { id label codeRncp }` and `info { totalRows }`.
- `getCertification(certificationId: ID!)`: certification details.

Requests use GraphQL variables, bounded timeouts, and no redirects. Failures log
only a generic message and exception type. The API currently represents unknown
certification IDs with a non-null-field error; the client maps that specific
response to a missing certification.

## Presentation

The `graphql_api/autocomplete` library customizes this field's jQuery UI instance,
keeping Drupal's AJAX source and keyboard selection. The menu matches the input
width. DSFR supplies the icons. Customize `css/autocomplete.css` using the
`s-cert` BEM block (`s-` is the project's search prefix).

## Verification

Live smoke checks (requires a running local DDEV site and upstream access):

```sh
ddev exec vendor/bin/drush php:script web/modules/custom/graphql_api/tests/live-check.php
```

Checks cover one-character search, distinct nine-result pages, autocomplete URLs,
rendered pager controls, query preservation, out-of-range pages, certification
rendering, missing/invalid IDs, and empty results.

The results page renders `CertificationResultsForm` directly, without the candidate
search block or the `fr-search-bar--lg` modifier. Its title follows the search
query. `graphql-api-results.html.twig` renders DSFR cards with the certificateur
and RNCP code, and `pager--graphql-search.html.twig` renders the DSFR pager.
Layout styles live in `css/search-results.css` under `s-cert-results` classes;
no Tailwind utilities are required.
