<div class="wrap-b">
    <h1>Dashboard</h1>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-1">Manage</a></li>
        <li><a href="#tab-2">Add New</a></li>
        <li><a href="#tab-3">Settings</a></li>
        
    </ul>

    <div class="tab-content">
        
        <div id="tab-1" class="tab-pane active">
            <h3>Dashboard</h3>
            <form method="post" action="options.php">
                <label for="number">Enter a number:</label>
                <input type="number" name="number" id="number" required>
                <button type="submit">Submit</button>
            </form>

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $number = $_POST["number"];
                echo "<h3>Number: $number</h3>";
            }
            ?>
        </div>

        <div id="tab-2" class="tab-pane">
            <h3>Add New Booking</h3>
        </div>

        <div id="tab-3" class="tab-pane">
            <h3>Settings</h3>
        </div>

    </div>

    
</div>