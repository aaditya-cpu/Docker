<?php
// admin/admin-page.php
include_once '/includes/db.php';
function your_plugin_admin_page() {
    global $wpdb;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve and sanitize form inputs
        // Make sure to include your sanitization logic here
        
        $room_type = sanitize_text_field($_POST['roomType']);
        $room_number = intval($_POST['roomNumber']);
        $price_per_night = floatval($_POST['pricePerNight']);
        $sale = sanitize_text_field($_POST['sale']);
        $discount = intval($_POST['discount']);
        // $check_in_date_time = sanitize_text_field($_POST['checkInDateTime']);
        // $check_out_date_time = sanitize_text_field($_POST['checkOutDateTime']);
        // $advanced_booking = sanitize_text_field($_POST['advanced_booking']);
        // // Sanitize form inputs
        // $room_type = sanitize_text_field($_POST['roomType']);
        // $room_number = intval($_POST['roomNumber']);
        // $price_per_night = floatval($_POST['pricePerNight']);
        // $sale = isset($_POST['sale']) ? 1 : 0;
        // $discount = floatval($_POST['discount']);
        $check_in_date_time = sanitize_text_field($_POST['checkInDate'] . ' ' . $_POST['checkInTime']); // Concatenate date and time
        $check_out_date_time = sanitize_text_field($_POST['checkOutDate'] . ' ' . $_POST['checkOutTime']); // Concatenate date and time
        $advanced_booking = isset($_POST['advancedBooking']) ? 1 : 0;
        $guest_name = sanitize_text_field($_POST['guestName']);
        $guest_email = sanitize_email($_POST['guestEmail']);
        $guest_phone = sanitize_text_field($_POST['guestPhone']);
        
        if (isset($_POST['edit_invoice']) && is_numeric($_POST['edit_invoice'])) {
            // Update existing invoice
            $invoice_id = intval($_POST['edit_invoice']);
            
            // Prepare the update query for the invoice_data table
            $wpdb->update(
                'invoice_data', // Table name
                array( // Data to update
                    'room_type' => $room_type,
                    'room_number' => $room_number,
                    'price_per_night' => $price_per_night,
                    'sale' => $sale,
                    'discount' => $discount,
                    'check_in_date_time' => $check_in_date_time,
                    'check_out_date_time' => $check_out_date_time,
                    'advanced_booking' => $advanced_booking
                ),
                array('id' => $invoice_id) // Where clause
            );

            // Prepare the update query for the personal_details table
            $wpdb->update(
                'personal_details',
                array(
                    'guest_name' => $guest_name,
                    'guest_email' => $guest_email,
                    'guest_phone' => $guest_phone
                ),
                array('invoice_id' => $invoice_id)
            );

            $message = 'Invoice updated successfully.';
        } else {
            // Insert new invoice
            // Insert data into the invoice_data table
            $wpdb->insert(
                'invoice_data',
                array(
                    'user_id' => $user_id, // Assuming this is auto-generated or fetched elsewhere
                    'room_type' => $room_type,
                    'room_number' => $room_number,
                    'price_per_night' => $price_per_night,
                    'sale' => $sale,
                    'discount' => $discount,
                    'check_in_date_time' => $check_in_date_time,
                    'check_out_date_time' => $check_out_date_time,
                    'advanced_booking' => $advanced_booking
                )
            );

            $invoice_id = $wpdb->insert_id; // Get the ID of the newly created invoice

            // Insert data into the personal_details table
            $wpdb->insert(
                'personal_details',
                array(
                    'invoice_id' => $invoice_id,
                    'guest_name' => $guest_name,
                    'guest_email' => $guest_email,
                    'guest_phone' => $guest_phone
                )
            );

            $message = 'Invoice created successfully.';
        }
    }

    if (isset($_GET['delete_invoice']) && is_numeric($_GET['delete_invoice'])) {
        $invoice_id = intval($_GET['delete_invoice']);
        $message = delete_invoice($invoice_id);
    }

    if (isset($_GET['edit_invoice']) && is_numeric($_GET['edit_invoice'])) {
        // Load the entry data and populate the form
        $invoice_id = intval($_GET['edit_invoice']);
        $invoice_data = get_invoice_data_by_id($invoice_id); // Implement this function
        // Populate form with $invoice_data
    }

    ?>
   <div class="container mt-5">
    <form method="POST" action="">
        <h2 class="mb-4">Invoice Form</h2>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="roomType" class="form-label">Room Type</label>
                <select class="form-select" name="roomType" id="roomType" required>
                    <option value="AC 2-seater">AC 2-seater</option>
                    <option value="AC 4-seater">AC 4-seater</option>
                    <option value="NONAC 2-seater">NONAC 2-seater</option>
                    <option value="NONAC 4-seater">NONAC 4-seater</option>
                    <option value="Hall">Hall</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="roomNumber" class="form-label">Room Number</label>
                <input type="number" class="form-control" name="roomNumber" id="roomNumber" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="pricePerNight" class="form-label">Price Per Night</label>
                <input type="number" class="form-control" name="pricePerNight" id="pricePerNight" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="discount" class="form-label">Discount Percentage</label>
                <input type="number" class="form-control" name="discount" id="discount">
            </div>
            <!-- Add other input fields as needed -->
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<div class="container mt-5">
    <form method="POST" action="">
        <h2 class="mb-4">Invoice Form</h2>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="checkInDateTime" class="form-label">Check-in Date & Time</label>
                <input type="datetime-local" class="form-control" name="checkInDateTime" id="checkInDateTime" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="checkOutDateTime" class="form-label">Check-out Date & Time</label>
                <input type="datetime-local" class="form-control" name="checkOutDateTime" id="checkOutDateTime" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>


<div class="container mt-5">
    <form method="POST" action="">
        <h2 class="mb-4">Invoice Form</h2>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="checkInDateTime" class="form-label">Check-in Date & Time</label>
                <input type="datetime-local" class="form-control" name="checkInDateTime" id="checkInDateTime" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="checkOutDateTime" class="form-label">Check-out Date & Time</label>
                <input type="datetime-local" class="form-control" name="checkOutDateTime" id="checkOutDateTime" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>


    <h2 class="mt-5 mb-4">Invoices</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Guest Name</th>
                <th>Room Type</th>
                <th>Check-in Date</th>
                <th>Check-out Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch and list the invoices here
            // Replace with your actual data fetching logic
            foreach ($invoices as $invoice) {
                echo '<tr>';
                echo '<td>' . $invoice->id . '</td>';
                echo '<td>' . $invoice->guest_name . '</td>';
                echo '<td>' . $invoice->guest_phone . '</td>';
                echo '<td>' . $invoice->room_type . '</td>';
                echo '<td>' . $invoice->check_in_date . '</td>';
                echo '<td>' . $invoice->check_out_date . '</td>';
                
                echo '<td>';
                echo '<a href="?edit_invoice=' . $invoice->id . '" class="btn btn-warning btn-sm">Edit</a> ';
                echo '<a href="?delete_invoice=' . $invoice->id . '" class="btn btn-danger btn-sm">Delete</a>';
                echo '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>

    <?php
}

// function delete_invoice($invoice_id) {
//     global $wpdb;

//     // Delete from personal_details table first due to foreign key
//     $wpdb->delete('personal_details', array('invoice_id' => $invoice_id));

//     // Delete from invoice_data table
//     $wpdb->delete('invoice_data', array('id' => $invoice_id));

//     return 'Invoice deleted successfully.';
// }
function delete_invoice($invoice_id) {
    global $wpdb;

    // Current timestamp to mark as deleted
    $deleted_at_value = current_time('mysql', true);

    // Soft delete from the personal_details table
    $wpdb->update(
        'personal_details',
        array('deleted_at' => $deleted_at_value), // Set the deleted_at field
        array('invoice_id' => $invoice_id)        // Where clause
    );

    // Soft delete from the invoice_data table
    $wpdb->update(
        'invoice_data',
        array('deleted_at' => $deleted_at_value), // Set the deleted_at field
        array('id' => $invoice_id)                // Where clause
    );

    return 'Invoice deleted successfully.';
}



function your_plugin_admin_menu() {
    add_submenu_page(
        'options-general.php',
        'Your Plugin Settings',
        'Your Plugin',
        'manage_options',
        'your-plugin-settings',
        'your_plugin_admin_page'
    );
}

add_action('admin_menu', 'your_plugin_admin_menu');
?>
<!-- $invoice_id = 123; // Replace with the actual invoice ID
$result = soft_delete_invoice($invoice_id);
echo $result; // Outputs the result of the operation -->
