"use strict";

function initMap() {
	function init() {
		const $ = jQuery;
		$("<div id='moova-map' style='height: 400px;' ></div>").insertBefore(".woocommerce-additional-fields__field-wrapper");
		const map = new google.maps.Map(document.getElementById("moova-map"), {
			center: {
				lat: -33.8688,
				lng: 151.2195
			},
			zoom: 13,
		});

		const marker = new google.maps.Marker({
			map,
			anchorPoint: new google.maps.Point(0, -29),
		});

		//Set autocomplete
		const types = ["billing", "shipping"];
		for (let type of types) {
			let autocomplete = setAutocomplete(map, type);
			let place = new Place(type, map, marker, autocomplete);
			autocomplete.addListener("place_changed", () => {
				place.changed()
			});
		}

		google.maps.event.addListener(map, 'click', function(event) {
			placeMarker(event.latLng, marker);
		});
	}

	function setAutocomplete(map, type) {
		const input = document.getElementById(type + "_address_1");
		const autocomplete = new google.maps.places.Autocomplete(input);
		autocomplete.bindTo("bounds", map);
		// Set the data fields to return when the user selects a place.
		autocomplete.setFields(["address_components", "geometry", "icon", "name"]);
		return autocomplete;
	}

	function placeMarker(location, marker) {
		let types = ['billing', 'shipping'];
		for (let type of types) {
			document.getElementById(type + '_moova_lat').value = location.lat();
			document.getElementById(type + '_moova_lng').value = location.lng();
		}

		marker.setPosition(location);
		setMoovaCustomFields();
	}

	class Place {
		constructor(type, map, marker, autocomplete) {
			this.type = type;
			this.marker = marker;
			this.map = map;
			this.autocomplete = autocomplete
		}

		changed() {
			if (this.type === 'billing' && document.getElementById('ship-to-different-address-checkbox').checked) {
				return
			}
			this.marker.setVisible(false);
			let place = this.autocomplete.getPlace();
			if (!place.geometry) {
				window.alert("No details available for input: '" + place.name + "'");
				return;
			}

			// If the place has a geometry, then present it on a map.
			if (place.geometry.viewport) {
				this.map.fitBounds(place.geometry.viewport);
			} else {
				this.map.setCenter(place.geometry.location);
				this.map.setZoom(17); // Why 17? Because it looks good.
			}

			this.marker.setPosition(place.geometry.location);
			this.marker.setVisible(true);

			document.getElementById(this.type + '_moova_lat').value = place.geometry.location.lat();
			document.getElementById(this.type + '_moova_lng').value = place.geometry.location.lng();

			let postalCode = place.address_components.find(element => element.types[0] === 'postal_code')
			let city = place.address_components.find(element => element.types[0] === "administrative_area_level_1")
			let country = place.address_components.find(element => element.types[0] === 'country')

			if (postalCode) {
				document.getElementById(this.type + '_postcode').value = postalCode.long_name;
			}
			document.getElementById(this.type + '_city').value = city.long_name;
			setMoovaCustomFields();
		}
	}

	init();
}

function setMoovaCustomFields() {
	let type = document.getElementById('ship-to-different-address-checkbox').checked ? 'shipping' : 'billing';
	jQuery.ajax({
		type: 'POST',
		url: wc_checkout_params.ajax_url,
		data: {
			'action': 'moova_custom_fields',
			'lat': jQuery("#" + type + "_moova_lat").val(),
			'lng': jQuery("#" + type + "_moova_lng").val(),
		},
		success: function(result) {
			jQuery('body').trigger('update_checkout');
		}
	});
}

if (jQuery("#billing_moova_lat").val() != '')
	setMoovaCustomFields();