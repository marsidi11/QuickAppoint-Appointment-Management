<div class="wrap-am">
    
    <h1>Settings</h1>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('booking_management_option_group');
        do_settings_sections('booking_management_settings');
        submit_button();
        ?>
    </form>
</div>