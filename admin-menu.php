<?php

/**
 * Adds a sub menu page under plugin admin page.
 */
function comment_filter_admin_menu() {
    add_submenu_page(
        'options-general.php',
        __('Comment URL Filter Settings', COMMENT_URL_FILTER),
        __('Comment Filter', COMMENT_URL_FILTER),
        'manage_options',
        COMMENT_URL_FILTER.'-settings',
        'comment_filter_settings'
    );
}

add_action('admin_menu', 'comment_filter_admin_menu');