
(function ($) {
	$(document).ready(function() {
		var container = $('#moova-rate-app');
		if (container.length) {
			container.find('a').click(function() {
				container.remove();
				var dataToSend = {
					action: 'set_rate_minimum_shippings',
					rate_action: $(this).attr('data-rate-action'),
					nonce: $(this).attr('data-moova-ajax-nonce')
				};

				$.post(container.attr('data-moova-ajax-url') ,dataToSend,function(res) {});

			});
		}
	});
})(jQuery);