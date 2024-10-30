<?php

/**
 * Options pages
 */

add_action('admin_init', 'comment_filter_options');

function comment_filter_options() {

    /**
     * Sections
     */

    add_settings_section(
        'comment_filter_options_section',       // section
        __('Common Settings', COMMENT_URL_FILTER),
        'comment_filter_options_section_callback',
        'comment_filter_options_page'           // page
    );

    /**
     * Fields
     */

    add_settings_field(
        'comment_filter_option_spam_keywords',  // id
        __('Keywords', COMMENT_URL_FILTER),
        'comment_filter_options_section_spam_keywords_fields_callback',
        'comment_filter_options_page',          // page
        'comment_filter_options_section'        // section
    );

    add_settings_field(
        'comment_filter_option_spam_url_threshold',  // id
        __('URL Count', COMMENT_URL_FILTER),
        'comment_filter_options_section_spam_url_threshold_fields_callback',
        'comment_filter_options_page',          // page
        'comment_filter_options_section'        // section
    );

    /**
     * Register sections and fields
     */
    register_setting('comment_filter_options_page', 'comment_filter_option_spam_keywords');
    register_setting('comment_filter_options_page', 'comment_filter_option_spam_url_threshold');
}

/**
 * Call back functions
 */

function comment_filter_options_section_callback() {
    echo '<p class="description">' . __('These options allow you to customize the behaviour of the plugin', COMMENT_URL_FILTER) . '.</p>';
}

function comment_filter_options_section_spam_keywords_fields_callback() {
    $comment_filter_option_spam_keywords = get_option('comment_filter_option_spam_keywords','');
    ?>

    <fieldset>
    <legend class="screen-reader-text"><span><?php echo __('Keywords', COMMENT_URL_FILTER);?></span></legend>

    <textarea
        id="comment_filter_option_spam_keywords"
        name="comment_filter_option_spam_keywords"
        rows="8"
        class="large-text"><?php echo $comment_filter_option_spam_keywords; ?></textarea>

    <p class="description"><?php echo __('Add your spam keyword or phrases. Important: each keyword or phrase must be saved in new line.', COMMENT_URL_FILTER);?></p>

    </fieldset><?php
}

function comment_filter_options_section_spam_url_threshold_fields_callback() {
    $comment_filter_option_spam_url_threshold = (get_option('comment_filter_option_spam_url_threshold', 0) == '' ?  -1 : get_option('comment_filter_option_spam_url_threshold'));
    ?>

    <fieldset>
    <legend class="screen-reader-text"><span><?php echo __('URL Count', COMMENT_URL_FILTER);?></span></legend>
    <input type="number"
           name="comment_filter_option_spam_url_threshold"
           id="comment_filter_option_spam_url_threshold"
           class="small-text ltr"
           step="1" min="-1"
           value="<?php echo $comment_filter_option_spam_url_threshold;?>">
    <p class="description"><?php echo __('Set limit (threshold) for URL count allowed in single comment ("-1" no limit).', COMMENT_URL_FILTER);?></p>

    </fieldset><?php
}