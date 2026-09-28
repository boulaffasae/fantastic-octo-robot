/**
 * @file
 * Presentation for the certification search autocomplete.
 */
(function ($, Drupal, once) {
  Drupal.behaviors.candidateCertificationSearch = {
    attach(context) {
      once('s-cert', '.s-cert__input', context).forEach((input) => {
        const widget = $(input).autocomplete('instance');
        widget.widget().addClass('s-cert__dropdown');
        widget._resizeMenu = function () {
          this.menu.element.outerWidth(this.element.outerWidth());
        };

        widget._renderMenu = function (menu, items) {
          items.forEach((item) => this._renderItemData(menu, item));
          this._renderItemData(menu, {
            value: this.term,
            label: Drupal.t('Voir tous les résultats'),
            allResults: true,
          });
        };

        widget._renderItem = function (menu, item) {
          const row = $('<li>').addClass('s-cert__item');
          const content = $('<div>').addClass('s-cert__option').appendTo(row);
          $('<span>', { 'aria-hidden': 'true' })
            .addClass(`s-cert__icon fr-icon--sm ${item.allResults ? 'fr-icon-search-line' : 'fr-icon-file-text-line'}`)
            .appendTo(content);

          if (item.allResults) {
            row.addClass('s-cert__item--all-results');
            $('<b>').text(this.term).appendTo(content);
            const url = new URL(input.dataset.autocompleteResultsUrl, window.location.origin);
            url.searchParams.set('q', this.term);
            $('<a>', { href: url.href, tabindex: -1 })
              .addClass('s-cert__link')
              .text(item.label)
              .append($('<span>', { 'aria-hidden': 'true' }).addClass('fr-icon--sm fr-icon-arrow-right-line'))
              .appendTo(content);
            item.url = url.href;
          }
          else {
            const details = $('<div>').addClass('s-cert__details').appendTo(content);
            const title = $('<span>').addClass('s-cert__label').appendTo(details);
            const term = Drupal.autocomplete.extractLastTerm(this.term) || '';
            const index = item.value.toLocaleLowerCase().indexOf(term.toLocaleLowerCase());
            // Build text nodes so API values and user input cannot inject HTML.
            if (term && index !== -1) {
              title.text(item.value.slice(0, index));
              $('<b>').text(item.value.slice(index, index + term.length)).appendTo(title);
              title.append(document.createTextNode(item.value.slice(index + term.length)));
            }
            else {
              title.text(item.value);
            }
            if (item.codeRncp) {
              $('<span>').addClass('s-cert__code')
                .text(`RNCP ${item.codeRncp.replace(/^RNCP\s*/i, '')}`).appendTo(details);
            }
          }
          return row.appendTo(menu);
        };

        const select = widget.option('select');
        widget.option('select', function (event, ui) {
          if (ui.item.url) {
            window.location.assign(ui.item.url);
            return false;
          }
          return select.call(this, event, ui);
        });
      });
    },
  };
})(jQuery, Drupal, once);
