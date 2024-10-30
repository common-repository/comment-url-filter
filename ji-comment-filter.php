<?php
/**
 * Plugin Name: Comment URL Filter
 * Plugin URI: http://lapuvieta.lv
 * Description: This plugin move comment to pending if there is no URL set. If URL field is not empty, comment is marked as spam.
 * Version: 1.5.2
 * Author: Janis Itkacs
 * Author URI: http://lapuvieta.lv
 * License: GPL2
 */

if (!defined( 'ABSPATH')) exit; // Exit if accessed directly

/**
 * Override wp_notify_moderator function
 * In order to disable notify messages on spam comments
 */
require_once ( plugin_dir_path( __FILE__ ) . 'notify.php');
require_once ( plugin_dir_path( __FILE__ ) . 'admin-menu.php');
require_once ( plugin_dir_path( __FILE__ ) . 'admin-settings.php');
require_once ( plugin_dir_path( __FILE__ ) . 'admin-options.php');

define('COMMENT_URL_FILTER', 'comment-url-filter');

add_action('comment_post', 'commentUrlFilter', 10, 2);

function commentUrlFilter($comment_ID, $comment_approved) {

    $comment = get_comment($comment_ID);
    $commentUpdate = get_comment($comment_ID, 'ARRAY_A');

    if ($comment_approved == 1) {
        return true;
    }

    // Validate comment
    if (validateComment($comment)) {
        return markSpam($commentUpdate);
    }

    // Spam was not found
    $commentUpdate['comment_approved'] = 0;
    wp_update_comment( $commentUpdate );

}

/**
 * Update comment as SPAM
 * @param $commentUpdate
 * @return bool
 */
function markSpam($commentUpdate) {
    $commentUpdate['comment_approved'] = 'spam';
    wp_update_comment( $commentUpdate );
    return true;
}

/**
 * Validate comment to check if it's a SPAM
 * @param $comment
 * @return bool
 */
function validateComment($comment) {
    return authorUrlTestForBlacklistedKeys()
                || commentContentContainURLTag($comment)
                || testAgainstSpamKeywords($comment)
                || testAgainstUrlThreshold($comment);
}

/**
 * Check if author URL field is empty.
 * If URL field is not empty, we assume it's a spam.
 * @param $comment
 * @return bool
 */
function authorUrlFieldNotEmpty($comment) {
    return !empty($comment->comment_author_url);
}

/**
 * Mark comment as SPAM if URL field starts with https, http or www.
 * @return bool
 */
function authorUrlTestForBlacklistedKeys() {

    $url = ( isset($_POST['url']) ? $_POST['url'] : '' );
    $blacklistedKeys = ['/^https?\:\/\//','/www\./', '/\.com/', '/\.net/', '/\.org/'];

    foreach($blacklistedKeys as $match) {
        if (preg_match($match, $url)) {
            return true;
        }
    }

    return false;
}

/**
 * Check if comment content field contains [url=*][/url] tags
 * If [url] tag is found, we assume it's a spam.
 * @param $comment
 * @return bool
 */
function commentContentContainURLTag($comment) {
    // Valid [url][/url] tag
    if (preg_match('/\[url\=.*\]*.\[\/url\]/i', $comment->comment_content)) {
        return true;
    }
    // Incomplete [url= tag
    if ($match = preg_match('/\[url\=/i', $comment->comment_content)) {
        return true;
    }
    return false;
}

/**
 * Check if comment contains any spam keywords saved by user
 * @param $comment
 * @return bool
 */
function testAgainstSpamKeywords($comment) {

    // Special characters
    $special = array('.',':', '/', ',', '#', '!', '$', '^', '@', '§', '%', '&', '*', '(', ')', '-', '+', '=', '|', '>', '<', '?', '£');
    $keywords = trim(get_option('comment_filter_option_spam_keywords'));

    // No keywords saved yet
    if (strlen($keywords) <= 0) {
        return false;
    }

    //Grab keywords
    $comment_filter_option_spam_keywords = explode(PHP_EOL, $keywords);

    // No keywords saved
    if (!is_array($comment_filter_option_spam_keywords)) {
        return false;
    }

    if (count($comment_filter_option_spam_keywords) <= 0) {
        return false;
    }

    $comment_content = str_replace($special, '', strip_tags(mb_strtolower($comment->comment_content)));

    foreach($comment_filter_option_spam_keywords as $keyword) {
        $keyword = trim(str_replace($special, '', mb_strtolower($keyword)));
        if (preg_match('/' . $keyword . '/m', $comment_content)) {
            return true;
        }
    }

    return false;

}

/**
 * Check against URL threshold count
 * @param $comment
 * @return bool
 */
function testAgainstUrlThreshold($comment) {

    //Grab threshold limit value
    $comment_filter_option_spam_url_threshold = intval(get_option('comment_filter_option_spam_url_threshold'));

    if ($comment_filter_option_spam_url_threshold < 0) {
        return false;
    }

    $comment_content = $comment->comment_content;

    if (preg_match_all('/https?\:\/\//', $comment_content, $comment_url_matches)) {
        return count($comment_url_matches[0]) >= $comment_filter_option_spam_url_threshold + 1;
    }

    return false;

}