<div class="wrap-b">
    <h1>Dashboard</h1>
    <?php settings_errors(); ?>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-1">Manage</a></li>
        <li><a href="#tab-2">Add New</a></li>
        <li><a href="#tab-3">Settings</a></li>
    </ul>

    <div class="tab-content">
        
        <div id="tab-1" class="tab-pane active">
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'booking_management_option_group' );
                    do_settings_sections( 'booking_management' );
                    submit_button();
                ?>
            </form>
        </div>

        <div id="tab-2" class="tab-pane">
            <h3>Add New Booking</h3>
        </div>

        <div id="tab-3" class="tab-pane">
            <h3>Settings</h3>
        </div>

    </div>

    
</div>