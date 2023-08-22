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
        $check_in_date_time = sanitize_text_field($_POST['checkInDateTime']);
        $check_out_date_time = sanitize_text_field($_POST['checkOutDateTime']);
        $advanced_booking = sanitize_text_field($_POST['advanced_booking']);
        // Sanitize other fields as well...

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
            // Rest of the code...
        } else {
            // Insert new invoice
            $wpdb->insert(
                'invoice_data',
                array(
                    'user_id' => $user_id,
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
            // Rest of the code...
        }
    }

    // Rest of the code...
?>
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
            <!-- Add other input fields as needed -->
        </div>
        <!-- Rest of the code... -->
    </form>
</div>

<?php
}
// Rest of the code...
?>
