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

  function showFreeShipping() {
    if ($("#has_free_shipping").val() == "0") {
      $("#free_shipping_price").closest("tr").hide();
    } else {
      $("#free_shipping_price").closest("tr").show();
    }
  }

  function showSpecialPricing() {
    $("#fixed_price").closest("tr").hide();
    $("#min_price").closest("tr").hide();
    $("#max_price").closest("tr").hide();

    var specialPrice = $("#has_special_price").val();
    if (specialPrice == "fixed") {
      $("#fixed_price").closest("tr").show();
    } else if (specialPrice == "range") {
      $("#min_price").closest("tr").show();
      $("#max_price").closest("tr").show();
    }
  }

  $("#google_place_id").closest("tr").hide();
  $("#address_autocomplete").autocomplete({
    source: function (request, response) {
      return autocomplete(request, response);
    },
    select: function (event, ui) {
      $(this).val(ui.item.label);
      $("#google_place_id").val(ui.item.value);
      return false;
    },
  });

  $("#has_free_shipping").change(showFreeShipping);
  $("#has_special_price").change(showSpecialPricing);
  showFreeShipping();
  showSpecialPricing();

  $('form').one('submit', function(e) {
    if (document.getElementById("google_place_id") && !$("#google_place_id").val()) {
      e.preventDefault()
        alert('Please select one address from the autocomplete');
    }
  });
  
})(jQuery, wc_moova_settings);
