define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    $(document).on('ajaxError', async function (e, obj) {
        var iframe;

        if (!obj.responseText.includes('window.ignite(window.data)')) {
            return;
        }

        iframe = $('<iframe style="border:0;width:100%;height:100%;"></iframe>');
        iframe
            .modal({
                modalClass: 'modal-ignition',
                autoOpen: true,
                buttons: []
            })
            .closest('.modal-content')
            .css({
                flexGrow: 1
            })
            .closest('.modal-inner-wrap')
            .css({
                maxWidth: '1600px',
                width: '100%',
                height: '95%'
            });

        // do not use "srcdoc" or "src" for working anchor links
        iframe[0].contentDocument.open();
        iframe[0].contentDocument.write(obj.responseText);
        iframe[0].contentDocument.close();
    });
});
