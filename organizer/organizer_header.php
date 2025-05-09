
<body>
    <div class="organizer_header">
        <div class="logo">
            <div class="logo_text">Evently</div>
        </div>
    </div>


        <div class="navigation_information">
            <div class="nav_bar">
                <ul class="nav_buttons">
                    <li class="nav_button"><a href="dashboard.php">Dashboard</a></li>
                    <li class="nav_button"><a href="events.php">My Events</a></li>
                    <li class="nav_button"><a href="create_event.php">Create Event</a></li>
                   
                </ul>
            </div>

            <div class="organizer_info">
                <?php
                    session_start();
                    echo "<p>".$_SESSION["user_name"]."</p>";
                ?>
                <a href="../php/logout.php">Logout</a>
            </div>
        </div>
        
    </div>
    
