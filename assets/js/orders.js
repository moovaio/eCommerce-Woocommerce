'use strict';

(function ($, settings) {
    $('[data-action="generate-order-shipping-label"]').click(function (e) {
        e.preventDefault();
        var btn = e.currentTarget;
        var dataToSend = {
            action: 'order_create_shipping_label',
            order_id: settings.order_id,
            nonce: settings.ajax_nonce
        };
        $.post(settings.ajax_url, dataToSend, function (data) {
            if (data.success) {
                btn.innerHTML = 'Etiqueta generada';
                $('#generate-order-shipping-label-error').hide();
            } else {
                btn.innerHTML = 'Generar etiqueta';
                btn.classList.remove('disabled');
                $('#generate-order-shipping-label-error').show();
            }
        });
        btn.innerHTML = 'Generando etiqueta...';
        btn.classList.add('disabled');
    });

    $('[data-action="process-order"]').click(function (e) {
        e.preventDefault();
        var btn = e.currentTarget;
        var dataToSend = {
            action: 'process_order',
            order_id: settings.order_id,
            nonce: settings.ajax_nonce
        };
        $.post(settings.ajax_url, dataToSend, function (data) {
            if (data.success) {
                btn.innerHTML = 'Pedido procesado';
                $('#process-order-error').hide();
            } else {
                btn.innerHTML = 'Procesar pedido';
                btn.classList.remove('disabled');
                $('#process-order-error').show();
            }
        });
        btn.innerHTML = 'Procesando pedido...';
        btn.classList.add('disabled');
    });
})(jQuery, wc_moova_settings);