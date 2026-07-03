(function ($) {
  if (typeof acf === 'undefined' || typeof akyosAccessReviews === 'undefined') {
    return;
  }

  function escHtml(str) {
    return $('<div>').text(str || '').html();
  }

  function truncate(str, max) {
    if (!str || str.length <= max) {
      return str || '';
    }

    return str.slice(0, max) + '…';
  }

  function getContext($root) {
    const $fields = $root.closest('.acf-fields');

    return {
      apiKeyField: acf.getField($fields.find('[data-name="gmb_api_key"]').first()),
      placeIdField: acf.getField($fields.find('[data-name="gmb_place_id"]').first()),
      reviewsField: acf.getField($fields.find('[data-name="reviews"]').first()),
    };
  }

  function getApiKey(ctx) {
    return ctx.apiKeyField ? ctx.apiKeyField.val() : '';
  }

  function existingGmbIds(reviewsField) {
    const ids = new Set();

    if (!reviewsField) {
      return ids;
    }

    reviewsField.$rows().each(function () {
      const field = acf.getField($(this).find('[data-name="gmb_id"]'));

      if (field && field.val()) {
        ids.add(field.val());
      }
    });

    return ids;
  }

  function setRowValue(row, name, value) {
    const field = row.getField ? row.getField(name) : acf.getField(row.find('[data-name="' + name + '"]'));

    if (field) {
      field.val(value);
    }
  }

  function initPlaceSearch(placeIdField) {
    if (!placeIdField || placeIdField.$el.data('akyos-search-init')) {
      return;
    }

    placeIdField.$el.data('akyos-search-init', true);

    const searchCtx = getContext(placeIdField.$el);

    const $wrap = $('<div class="akyos-gmb-search"></div>');
    const $input = $(
      '<input type="search" class="akyos-gmb-search__input regular-text" placeholder="Ex. Clinique Clerion Lyon" autocomplete="off" />'
    );
    const $btn = $('<button type="button" class="button button-secondary">Rechercher</button>');
    const $results = $('<div class="akyos-gmb-search__results"></div>');
    const $selected = $('<p class="akyos-gmb-search__selected description"></p>');

    $wrap.append($('<div class="akyos-gmb-search__row"></div>').append($input, $btn), $results, $selected);
    placeIdField.$el.find('.acf-input').prepend($wrap);

    function showSelected(label) {
      $selected.text(label || '');
    }

    if (placeIdField.val()) {
      showSelected('Place ID actuel : ' + placeIdField.val());
    }

    function runSearch() {
      const query = $input.val().trim();

      if (!query) {
        $results.html('<p class="description">Saisissez un nom ou une adresse.</p>');
        return;
      }

      $results.html('<p class="description">Recherche en cours…</p>');

      $.post(akyosAccessReviews.ajaxUrl, {
        action: 'akyos_access_search_places',
        nonce: akyosAccessReviews.nonce,
        query: query,
        api_key: getApiKey(searchCtx),
      })
        .done(function (response) {
          if (!response.success) {
            $results.html('<p class="description">' + escHtml(response.data?.message || 'Erreur') + '</p>');
            return;
          }

          const places = response.data.places || [];

          if (places.length === 0) {
            $results.html('<p class="description">Aucun établissement trouvé.</p>');
            return;
          }

          const $list = $('<div class="akyos-gmb-search__list"></div>');

          places.forEach(function (place) {
            const $placeBtn = $('<button type="button" class="akyos-gmb-search__place"></button>');
            const rating = place.rating ? ' — ' + place.rating + '/5' : '';
            const total = place.total ? ' (' + place.total + ' avis Google)' : '';

            $placeBtn.html(
              '<strong>' +
                escHtml(place.name) +
                '</strong><br><small>' +
                escHtml(place.address) +
                rating +
                total +
                '</small>'
            );

            $placeBtn.on('click', function () {
              placeIdField.val(place.place_id);
              showSelected('Établissement sélectionné : ' + place.name);
              $results.empty();
              $input.val(place.name);
            });

            $list.append($placeBtn);
          });

          $results.empty().append($list);
        })
        .fail(function (xhr) {
          const message = xhr.responseJSON?.data?.message || 'Erreur réseau';
          $results.html('<p class="description">' + escHtml(message) + '</p>');
        });
    }

    $btn.on('click', runSearch);
    $input.on('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        runSearch();
      }
    });
  }

  function renderImporter($container) {
    if ($container.data('initialized')) {
      return;
    }

    $container.data('initialized', true);

    const ctx = getContext($container);
    initPlaceSearch(ctx.placeIdField);

    const $toolbar = $('<div class="akyos-gmb-importer__toolbar"></div>');
    const $fetchBtn = $('<button type="button" class="button button-secondary">Récupérer les avis Google</button>');
    const $status = $('<p class="akyos-gmb-importer__status description"></p>');
    const $list = $('<div class="akyos-gmb-importer__list"></div>');
    const $importBtn = $('<button type="button" class="button button-primary akyos-gmb-importer__import">Importer la sélection</button>');

    $toolbar.append($fetchBtn);
    $container.append($toolbar, $status, $list, $importBtn.hide());

    let fetchedReviews = [];

    $fetchBtn.on('click', function () {
      const placeId = ctx.placeIdField ? ctx.placeIdField.val() : '';

      $status.text('Récupération en cours…');
      $list.empty();
      $importBtn.hide();
      fetchedReviews = [];

      $.post(akyosAccessReviews.ajaxUrl, {
        action: 'akyos_access_fetch_gmb_reviews',
        nonce: akyosAccessReviews.nonce,
        place_id: placeId,
        api_key: getApiKey(ctx),
      })
        .done(function (response) {
          if (!response.success) {
            $status.text(response.data?.message || 'Erreur inconnue');
            return;
          }

          const data = response.data;
          fetchedReviews = data.reviews || [];
          const existing = existingGmbIds(ctx.reviewsField);
          const placeLabel = data.place_name ? data.place_name + ' — ' : '';
          const ratingLabel = data.rating ? ' (' + data.rating + '/5, ' + (data.total || '?') + ' au total sur Google)' : '';

          $status.text(placeLabel + fetchedReviews.length + ' avis récupérés' + ratingLabel);

          if (fetchedReviews.length === 0) {
            return;
          }

          fetchedReviews.forEach(function (review, index) {
            const isDup = existing.has(review.gmb_id);
            const $item = $('<label class="akyos-gmb-importer__item"></label>');
            const $checkbox = $('<input type="checkbox" />').val(String(index));

            if (isDup) {
              $checkbox.prop({ checked: false, disabled: true });
            } else {
              $checkbox.prop('checked', true);
            }

            $item.append(
              $checkbox,
              $('<span class="akyos-gmb-importer__label"></span>').html(
                '<strong>' +
                  escHtml(review.author) +
                  '</strong> — ' +
                  review.rating +
                  '/5' +
                  (review.relative_time ? ' <small>(' + escHtml(review.relative_time) + ')</small>' : '') +
                  '<br><em>' +
                  escHtml(truncate(review.text, 140)) +
                  '</em>' +
                  (isDup ? '<br><small>Déjà importé</small>' : '')
              )
            );

            $list.append($item);
          });

          if ($list.find('input:not(:disabled)').length > 0) {
            $importBtn.show();
          }
        })
        .fail(function (xhr) {
          const message = xhr.responseJSON?.data?.message || 'Erreur réseau';
          $status.text(message);
        });
    });

    $importBtn.on('click', function () {
      const selected = [];

      $list.find('input:checked:not(:disabled)').each(function () {
        selected.push(fetchedReviews[parseInt($(this).val(), 10)]);
      });

      if (selected.length === 0) {
        $status.text('Sélectionnez au moins un avis.');
        return;
      }

      if (!ctx.reviewsField) {
        $status.text('Répéteur Avis introuvable.');
        return;
      }

      $importBtn.prop('disabled', true);
      $status.text('Import en cours (téléchargement des photos)…');

      $.post(akyosAccessReviews.ajaxUrl, {
        action: 'akyos_access_import_gmb_reviews',
        nonce: akyosAccessReviews.nonce,
        reviews: JSON.stringify(selected),
      })
        .done(function (response) {
          if (!response.success) {
            $status.text(response.data?.message || 'Erreur import');
            $importBtn.prop('disabled', false);
            return;
          }

          const imported = response.data.reviews || [];

          imported.forEach(function (review) {
            const row = ctx.reviewsField.add();

            setRowValue(row, 'author', review.author);
            setRowValue(row, 'rating', review.rating);
            setRowValue(row, 'text', review.text);
            setRowValue(row, 'date', review.date);
            setRowValue(row, 'gmb_id', review.gmb_id);

            if (review.photo) {
              setRowValue(row, 'photo', review.photo);
            }
          });

          $status.text(imported.length + ' avis importés — modifiables librement dans l’onglet Avis.');
          $list.empty();
          $importBtn.hide().prop('disabled', false);
        })
        .fail(function (xhr) {
          const message = xhr.responseJSON?.data?.message || 'Erreur réseau';
          $status.text(message);
          $importBtn.prop('disabled', false);
        });
    });
  }

  function boot($el) {
    $el.find('[data-akyos-gmb-importer]').each(function () {
      renderImporter($(this));
    });
  }

  acf.addAction('ready_field/name=gmb_place_id', initPlaceSearch);

  acf.addAction('ready', function () {
    boot($(document));
  });

  acf.addAction('append', boot);
})(jQuery);
