<div class="wrap-am">
    
    <h1>Settings</h1>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
            settings_fields('am_options_data');
            do_settings_sections('appointment_management');
            submit_button();
        ?>
    </form>
</div>