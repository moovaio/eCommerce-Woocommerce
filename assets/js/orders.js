'use strict';

(function ($, settings) {
    $('[data-action="generate_order_shipping_label"]').click(function (e) {
        handleMetaboxButtonClick(e, {
            current: settings.text_shipping_label_current,
            completed: settings.text_shipping_label_completed,
            btnName: settings.text_shipping_label_name
        });
    });

    $('[data-action="process_order"]').click(function (e) {
        handleMetaboxButtonClick(e, {
            current: settings.text_order_current,
            completed: settings.text_order_completed,
            btnName: settings.text_order_name
        });
    });

    $('[data-action="change_order_status"]').click(function (e) {
        handleMetaboxButtonClick(e, {
            current: settings.text_order_status_current,
            completed: settings.text_order_status_completed,
            btnName: settings.text_order_status_name,
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
        location.append('<h4 style="margin-bottom: 0;color: #e80202;" id="' + errName + '">' + settings.text_error + '</h4>');
    }

    function removeError(errName) {
        if ($('#' + errName).length > 0) {
            $('#' + errName).remove();
        }
    }
})(jQuery, wc_moova_settings);