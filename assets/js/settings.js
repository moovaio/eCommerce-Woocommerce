"use strict";

var settings = wc_moova_settings;

(function ($, settings) {
  function autocomplete(request, response) {
    var dataToSend = {
      nonce: settings.ajax_nonce,
      action: "get_autocomplete_street",
      query: request,
    };
    jQuery.post(settings.ajax_url, dataToSend, function (res) {
      var limitedAutocomplete = res.data.slice(0, 5);
      var res = $.map(limitedAutocomplete, function (value, key) {
        return {
          label: value.main_text + ", " + value.secondary_text,
          value: value.place_id,
        };
      });

      response(res);
    });
  }
  $("#tags").autocomplete({
    source: function (request, response) {
      return autocomplete(request, response);
    },
  });
})(jQuery, wc_moova_settings);
