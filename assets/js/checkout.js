"use strict";

function initMap() {
  const $ = jQuery;
  const mapExists = true;
    $("<div id='moova-map' style='height: 400px;' ></div>").insertBefore(".woocommerce-additional-fields__field-wrapper");
    const map = new google.maps.Map(document.getElementById("moova-map"), {
      center: { lat: -33.8688, lng: 151.2195 },
      zoom: 13,
    });
  
  const fieldToListen = true ? 'billing' : 'shipping';
  const input = document.getElementById("billing_address_1");
  const autocomplete = new google.maps.places.Autocomplete(input);
  autocomplete.bindTo("bounds", map);
  // Set the data fields to return when the user selects a place.
  autocomplete.setFields(["address_components", "geometry", "icon", "name"]);
  const marker = new google.maps.Marker({
    map,
    anchorPoint: new google.maps.Point(0, -29),
  });

  autocomplete.addListener("place_changed", () => { 
    marker.setVisible(false);
    const place = autocomplete.getPlace();

    if (!place.geometry) {
      window.alert("No details available for input: '" + place.name + "'");
      return;
    }

    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport) {
      map.fitBounds(place.geometry.viewport);
    } else {
      map.setCenter(place.geometry.location);
      map.setZoom(17); // Why 17? Because it looks good.
    }

    marker.setPosition(place.geometry.location);
    marker.setVisible(true);
    let address = "";

    if (place.address_components) {
      address = [
        (place.address_components[0] &&
          place.address_components[0].short_name) ||
          "",
        (place.address_components[1] &&
          place.address_components[1].short_name) ||
          "",
        (place.address_components[2] &&
          place.address_components[2].short_name) ||
          "",
      ].join(" ");
    }
    infowindowContent.children["place-icon"].src = place.icon;
    infowindowContent.children["place-name"].textContent = place.name;
    infowindowContent.children["place-address"].textContent = 'address';
    infowindow.open(map, marker);
  });

  google.maps.event.addListener(map, 'click', function(event) {
   placeMarker(event.latLng,marker);
});
  
  function placeMarker(location, marker) {
    document.getElementById('billing_moova_lat').value = location.lat();
    document.getElementById('billing_moova_lng').value = location.lng();
    document.getElementById('shipping_moova_lat').value = location.lat();
    document.getElementById('shipping_moova_lng').value = location.lng();

    marker.setPosition(location);
  }
}

//Listen change on ship-to-different-address-checkbox