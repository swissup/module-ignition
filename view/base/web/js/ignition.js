define([
    'jquery'
], function ($) {
    'use strict';

    function openModal(content) {
        var iframe = $('<iframe style="border:0;width:100%;height:100%;"></iframe>');

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
                maxHeight: 'none',
                width: '100%',
                height: '95%'
            })
            .closest('.modal-ignition')
            .css({
                zIndex: 999999
            });

        // do not use "srcdoc" or "src" for working anchor links
        iframe[0].contentDocument.open();
        iframe[0].contentDocument.write(content);
        iframe[0].contentDocument.close();
    }

    $(document).on('ajaxError', async function (e, obj) {
        if (!obj.responseText?.includes('window.ignite(window.data)')) {
            return;
        }

        require(['Magento_Ui/js/modal/modal'], () => openModal(obj.responseText));
    });
});
