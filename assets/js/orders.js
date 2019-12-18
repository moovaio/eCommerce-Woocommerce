'use strict';

(function ($, settings) {
    $('[data-action="generate_order_shipping_label"]').click(function (e) {
        handleMetaboxButtonClick(e, {
            current: 'Generando etiqueta...',
            completed: 'Etiqueta generada',
            btnName: 'Generar etiqueta'
        });
    });

    $('[data-action="process_order"]').click(function (e) {
        handleMetaboxButtonClick(e, {
            current: 'Procesando pedido...',
            completed: 'Pedido procesado',
            btnName: 'Procesar pedido'
        });
    });

    $('[data-action="change_order_status"]').click(function (e) {
        handleMetaboxButtonClick(e, {
            current: 'Actualizando el estado del pedido...',
            completed: 'Pedido actualizado',
            btnName: 'Marcar pedido como listo para enviar'
        });
    });

    function handleMetaboxButtonClick(e, messages) {
        e.preventDefault();
        var btn = e.currentTarget;
        var btnData = $(btn).data();
        var dataToSend = {
            order_id: settings.order_id,
            nonce: settings.ajax_nonce
        };
        $.extend(dataToSend, btnData);
        var errName = dataToSend.action + '-error';
        $.post(settings.ajax_url, dataToSend, function (data) {
            if (data.success) {
                btn.innerHTML = messages.completed;
                removeError(errName)
                setTimeout(function () {
                    location.reload();
                }, 1000);
            } else {
                btn.innerHTML = messages.btnName;
                btn.classList.remove('disabled');
                addError($(btn).parent(), errName);
            }
        });
        btn.innerHTML = messages.current;
        btn.classList.add('disabled');
    }

    function addError(location, errName) {
        removeError(errName);
        location.append('<h4 style="margin-bottom: 0;color: #e80202;" id="' + errName + '">Hubo un error, por favor intenta nuevamente</h4>');
    }

    function removeError(errName) {
        if ($('#' + errName).length > 0) {
            $('#' + errName).remove();
        }
    }
})(jQuery, wc_moova_settings);