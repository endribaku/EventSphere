<?php
    ob_start(); 
    require_once("organizer_auth.php");
    require_once("../php/db.php");
    require_once("organizer_header.php");

    if (isset($_GET['status'])) {
        if ($_GET['status'] === 'created') {
            echo '<div class="alert alert-success">Event created successfully.</div>';
        } elseif ($_GET['status'] === 'notcreated') {
            echo '<div class="alert alert-danger">Failed to create event.</div>';
        }  elseif ($_GET['status'] === 'overlap') {
            echo '<div class="alert alert-danger">There is already an event scheduled at this venue on that date. Please choose another date or venue.</div>';
        }
    }
?>


<div class="create-event-container">
    <h2 class="section-title">Create New Event</h2>
    
    <?php
    
    ?>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-calendar-plus"></i> Event Details</h3>
        </div>
        <div class="card-body">
            <form action="create_event.php" method="POST" enctype="multipart/form-data" class="create-event-form">
                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" name="title" id="title" class="form-input" placeholder="Enter a descriptive title" required>
                    <small class="form-text">Choose a clear, descriptive title for your event</small>
                </div>

                <div class="form-group">
                    <label for="description">Event Description</label>
                    <textarea name="description" id="description" rows="5" class="form-textarea" placeholder="Describe your event in detail" required></textarea>
                    <small class="form-text">Provide details about your event, what attendees can expect, etc.</small>
                </div>

                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="date">Event Date</label>
                        <input type="date" name="date" id="date" class="form-input" required min="<?= date('Y-m-d') ?>">
                        <small class="form-text">When will your event take place?</small>
                    </div>

                    <div class="form-group form-group-half">
                        <label for="price">Ticket Price ($)</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" class="form-input" placeholder="0.00" required>
                        <small class="form-text">How much will tickets cost? (Enter 0 for free events)</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="venue_id">Venue</label>
                        <select name="venue_id" id="venue_id" class="form-select" required>
                            <option value="">Select a venue</option>
                            <?php
                            $venuesQuery = "SELECT * FROM venues ORDER BY name ASC";
                            $result = mysqli_query($conn, $venuesQuery);
                            while ($venue = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$venue['id']}'>{$venue['name']} ({$venue['location']}, capacity: {$venue['capacity']})</option>";
                            }
                            ?>
                            <option value="new">+ Create New Venue</option>
                        </select>
                        <small class="form-text">Where will your event be held?</small>
                    </div>
                
                    <div id="new-venue-fields" style="display:none; margin-top: 1em;">
                    <div class="form-group">
                        <label for="new_venue_name">New Venue Name</label>
                        <input type="text" name="new_venue_name" id="new_venue_name" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select id="country" name="country" class="form-select">
                            <option value="">Select a country</option>
                            <?php 
                            require_once("../misc/countries.list.php");
                            foreach($countries as $country) {
                                echo '<option value="' . htmlspecialchars($country) . '">' . htmlspecialchars($country) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new_venue_location">Location</label>
                        <input type="text" name="new_venue_location" id="new_venue_location" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="new_venue_capacity">Capacity</label>
                        <input type="number" name="new_venue_capacity" id="new_venue_capacity" class="form-input" min="1">
                    </div>
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="category_id">Event Category</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">Select a category</option>
                            <?php
                            $categoryQuery = "SELECT * from event_categories ORDER BY name ASC";
                            $result = mysqli_query($conn, $categoryQuery);
                            while ($category = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$category['id']}'>{$category['name']}</option>";
                            }
                            ?>
                        </select>
                        <small class="form-text">What type of event is this?</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Event Image</label>
                    <input type="file" name="image" id="image" class="form-input">
                    <small class="form-text">Upload an image to represent your event (JPG, PNG, GIF, max 5MB)</small>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Event</button>
                    <a href="events.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Process form submission
if (isset($_POST['submit'])) {
    // Validate and sanitize inputs
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $organizer_id = $_SESSION['user_id']; 
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    // if create venue

    if ($_POST['venue_id'] === 'new') {
        $newVenueName = trim($_POST['new_venue_name']);
        $newVenueLocation = trim($_POST['new_venue_location']);
        $newVenueCapacity = (int)$_POST['new_venue_capacity'];
        $newVenueCountry = trim($_POST['country']);
    
        if (empty($newVenueName) || empty($newVenueLocation) || empty($newVenueCountry) || $newVenueCapacity <= 0) {
            $_SESSION['event_error'] = "Please provide all details for the new venue including country.";
            header("Location: create_event.php");
            exit();
        }
    
        $insertVenueQuery = "INSERT INTO venues (name, location, country, capacity) VALUES (?, ?, ?, ?)";
        $venueStmt = mysqli_prepare($conn, $insertVenueQuery);
        mysqli_stmt_bind_param($venueStmt, "sssi", $newVenueName, $newVenueLocation, $newVenueCountry, $newVenueCapacity);
    
        if (!mysqli_stmt_execute($venueStmt)) {
            $_SESSION['event_error'] = "Failed to create new venue.";
            header("Location: create_event.php");
            exit();
        }
    
        $venue_id = mysqli_insert_id($conn);
    } else {
        $venue_id = (int)$_POST['venue_id'];
    }

    // Validate required fields
    if (empty($title) || empty($description) || empty($date) || empty($venue_id) || empty($category_id)) {
        $_SESSION['event_error'] = "All fields are required.";
        header("Location: create_event.php");
        exit();
    }

    $conflictQuery = "SELECT id FROM events WHERE venue_id = ? AND date = ?";
    $conflictStmt = mysqli_prepare($conn, $conflictQuery);
    mysqli_stmt_bind_param($conflictStmt, "is", $venue_id, $date);
    mysqli_stmt_execute($conflictStmt);
    $conflictResult = mysqli_stmt_get_result($conflictStmt);

    if (mysqli_num_rows($conflictResult) > 0) {

        header("Location: create_event.php?status=overlap");
        exit();
    }
    

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        
        // Create directory if it doesn't exist
        if (!is_dir('../images/events')) {
            mkdir('../images/events', 0777, true);
        }
        
 
        $uniqueName = time() . '_' . $imageName;
        $imagePath = '../images/events/' . $uniqueName;
        $fullPath = $imagePath;


        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['event_error'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            header("Location: create_event.php");
            exit();
        }
        
        if ($_FILES['image']['size'] > 5000000) { 
            $_SESSION['event_error'] = "File is too large. Maximum size is 5MB.";
            header("Location: create_event.php");
            exit();
        }
        

        if (!move_uploaded_file($imageTmp, $fullPath)) {
            $_SESSION['event_error'] = "Failed to upload image. Please try again.";
            header("Location: create_event.php");
            exit();
        }
    }


    $insertQuery = "INSERT INTO events (organizer_id, title, description, date, venue_id, image, category_id, price)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "isssisid", $organizer_id, $title, $description, $date, $venue_id, $imagePath, $category_id, $price);
    $result = mysqli_stmt_execute($stmt);

    if($result) {
        $_SESSION['event_success'] = "Event created successfully!";
        header("Location: events.php?status=created");
        exit();
    } else {
        $_SESSION['event_error'] = "Error creating event: " . mysqli_stmt_error($stmt);
        header("Location: create_event.php");
        exit();
    }
}
?>

<?php include_once("../footer.php");?>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const venueSelect = document.getElementById('venue_id');
    const newVenueFields = document.getElementById('new-venue-fields');

    if (venueSelect && newVenueFields) {
        venueSelect.addEventListener('change', function () {
            if (this.value === 'new') {
                newVenueFields.style.display = 'block';
            } else {
                newVenueFields.style.display = 'none';
            }
        });
    }
});
</script>



