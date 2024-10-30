<?php

function comment_filter_settings() { ?>

    <div class="wrap">
        <h1><?php echo get_admin_page_title(); ?></h1>
        <p><?php echo __('Options page for the plugin', COMMENT_URL_FILTER); ?>.</p>

        <form method="post" action="options.php">
            <?php

            settings_fields('comment_filter_options_page');
            do_settings_sections('comment_filter_options_page');

            submit_button();
            ?>
        </form>

    </div>

<?php }