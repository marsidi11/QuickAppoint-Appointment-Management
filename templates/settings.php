<div class="quickappoint">
    
    <h1>Settings</h1>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
            settings_fields('quickappointoptions_data');
            do_settings_sections('quickappoint');
            submit_button();
        ?>
    </form>
</div>