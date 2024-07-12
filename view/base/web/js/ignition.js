define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    document.head.appendChild(document.createElement('style')).innerHTML = `
        .modal-ignition {
            --ignition-modal-gap: 20px;
            .breeze-theme & {
                --ignition-modal-gap: 0px;
            }

            z-index: 999999;

            .modal-inner-wrap {
                left: 0 !important;
                margin: var(--ignition-modal-gap) auto !important;
                width: calc(100% - var(--ignition-modal-gap) * 2) !important;
                height: calc(100% - var(--ignition-modal-gap) * 2) !important;
                max-width: 1600px !important;
                max-height: none !important;
            }

            .modal-content {
                flex-grow: 1;
            }

            iframe {
                border: 0;
                width: 100%;
                height: 100%;
            }
        }
    `;

    function openModal(content) {
        var iframe = $('<iframe></iframe>');

        iframe.modal({
            modalClass: 'modal-ignition',
            autoOpen: true,
            buttons: []
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
