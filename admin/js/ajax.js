// ajax.js

var data = {
    'action': wp_ajax_data.action,
    '_ajax_nonce': wp_ajax_data.nonce
};

jQuery.post(ajaxurl, data);