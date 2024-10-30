<?php

if ( !function_exists('wp_notify_moderator') ) :
    /**
     * Notifies the moderator of the site about a new comment that is awaiting approval.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * Uses the {@see 'notify_moderator'} filter to determine whether the site moderator
     * should be notified, overriding the site setting.
     *
     * @param int $comment_id Comment ID.
     * @return true Always returns true.
     */
    function wp_notify_moderator($comment_id) {
        global $wpdb;

        $maybe_notify = get_option( 'moderation_notify' );

        /**
         * Filter whether to send the site moderator email notifications, overriding the site setting.
         *
         * @since 4.4.0
         *
         * @param bool $maybe_notify Whether to notify blog moderator.
         * @param int  $comment_ID   The id of the comment for the notification.
         */
        $maybe_notify = apply_filters( 'notify_moderator', $maybe_notify, $comment_id );

        if ( ! $maybe_notify ) {
            return true;
        }

        $comment = get_comment($comment_id);

        /**
         * Do not notify moderator, if comment is marked as spam
         */
        if (validateComment($comment)) {
            return true;
        }

        $post = get_post($comment->comment_post_ID);
        $user = get_userdata( $post->post_author );
        // Send to the administration and to the post author if the author can modify the comment.
        $emails = array( get_option( 'admin_email' ) );
        if ( $user && user_can( $user->ID, 'edit_comment', $comment_id ) && ! empty( $user->user_email ) ) {
            if ( 0 !== strcasecmp( $user->user_email, get_option( 'admin_email' ) ) )
                $emails[] = $user->user_email;
        }

        $comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
        $comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");

        // The blogname option is escaped with esc_html on the way into the database in sanitize_option
        // we want to reverse this for the plain text arena of emails.
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $comment_content = wp_specialchars_decode( $comment->comment_content );

        switch ( $comment->comment_type ) {
            case 'trackback':
                $notify_message  = sprintf( __('A new trackback on the post "%s" is waiting for your approval'), $post->post_title ) . "\r\n";
                $notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
                /* translators: 1: website name, 2: website IP, 3: website hostname */
                $notify_message .= sprintf( __( 'Website: %1$s (IP: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                $notify_message .= sprintf( __( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
                $notify_message .= __('Trackback excerpt: ') . "\r\n" . $comment_content . "\r\n\r\n";
                break;
            case 'pingback':
                $notify_message  = sprintf( __('A new pingback on the post "%s" is waiting for your approval'), $post->post_title ) . "\r\n";
                $notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
                /* translators: 1: website name, 2: website IP, 3: website hostname */
                $notify_message .= sprintf( __( 'Website: %1$s (IP: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                $notify_message .= sprintf( __( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
                $notify_message .= __('Pingback excerpt: ') . "\r\n" . $comment_content . "\r\n\r\n";
                break;
            default: // Comments
                $notify_message  = sprintf( __('A new comment on the post "%s" is waiting for your approval'), $post->post_title ) . "\r\n";
                $notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
                $notify_message .= sprintf( __( 'Author: %1$s (IP: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                $notify_message .= sprintf( __( 'Email: %s' ), $comment->comment_author_email ) . "\r\n";
                $notify_message .= sprintf( __( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
                $notify_message .= sprintf( __( 'Comment: %s' ), "\r\n" . $comment_content ) . "\r\n\r\n";
                break;
        }

        $notify_message .= sprintf( __( 'Approve it: %s' ), admin_url( "comment.php?action=approve&c={$comment_id}#wpbody-content" ) ) . "\r\n";

        if ( EMPTY_TRASH_DAYS )
            $notify_message .= sprintf( __( 'Trash it: %s' ), admin_url( "comment.php?action=trash&c={$comment_id}#wpbody-content" ) ) . "\r\n";
        else
            $notify_message .= sprintf( __( 'Delete it: %s' ), admin_url( "comment.php?action=delete&c={$comment_id}#wpbody-content" ) ) . "\r\n";

        $notify_message .= sprintf( __( 'Spam it: %s' ), admin_url( "comment.php?action=spam&c={$comment_id}#wpbody-content" ) ) . "\r\n";

        $notify_message .= sprintf( _n('Currently %s comment is waiting for approval. Please visit the moderation panel:',
                'Currently %s comments are waiting for approval. Please visit the moderation panel:', $comments_waiting), number_format_i18n($comments_waiting) ) . "\r\n";
        $notify_message .= admin_url( "edit-comments.php?comment_status=moderated#wpbody-content" ) . "\r\n";

        $subject = sprintf( __('[%1$s] Please moderate: "%2$s"'), $blogname, $post->post_title );
        $message_headers = '';

        /**
         * Filter the list of recipients for comment moderation emails.
         *
         * @since 3.7.0
         *
         * @param array $emails     List of email addresses to notify for comment moderation.
         * @param int   $comment_id Comment ID.
         */
        $emails = apply_filters( 'comment_moderation_recipients', $emails, $comment_id );

        /**
         * Filter the comment moderation email text.
         *
         * @since 1.5.2
         *
         * @param string $notify_message Text of the comment moderation email.
         * @param int    $comment_id     Comment ID.
         */
        $notify_message = apply_filters( 'comment_moderation_text', $notify_message, $comment_id );

        /**
         * Filter the comment moderation email subject.
         *
         * @since 1.5.2
         *
         * @param string $subject    Subject of the comment moderation email.
         * @param int    $comment_id Comment ID.
         */
        $subject = apply_filters( 'comment_moderation_subject', $subject, $comment_id );

        /**
         * Filter the comment moderation email headers.
         *
         * @since 2.8.0
         *
         * @param string $message_headers Headers for the comment moderation email.
         * @param int    $comment_id      Comment ID.
         */
        $message_headers = apply_filters( 'comment_moderation_headers', $message_headers, $comment_id );

        foreach ( $emails as $email ) {
            @wp_mail( $email, wp_specialchars_decode( $subject ), $notify_message, $message_headers );
        }

        return true;
    }
endif;

