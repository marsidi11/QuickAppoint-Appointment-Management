<div class="wrap-am">
    <h1>Settings</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('booking_management_option_group');
        do_settings_sections('booking_management');
        submit_button();
        ?>
    </form>
</div>