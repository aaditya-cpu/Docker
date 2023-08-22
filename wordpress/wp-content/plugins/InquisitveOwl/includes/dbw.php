<?php
// Database configuration
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "your_database";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Log the error instead of displaying it
    error_log("Connection failed: " . $conn->connect_error);
    die("An error occurred. Please try again later.");
}

// Create invoice_data table if not exists
$invoice_data_sql = "CREATE TABLE IF NOT EXISTS invoice_data (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    room_type ENUM('AC 2-seater', 'AC 4-seater', 'NONAC 2-seater', 'NONAC 4-seater', 'Hall') NOT NULL,
    room_number INT(11) NOT NULL,
    price_per_night DECIMAL(10, 2) NOT NULL,
    sale BOOLEAN DEFAULT FALSE,
    discount DECIMAL(5, 2),
    check_in_datetime DATETIME NOT NULL,
    check_out_datetime DATETIME NOT NULL,
    deleted_at DATETIME
)";

if ($conn->query($invoice_data_sql) !== TRUE) {
    error_log("Error creating invoice_data table: " . $conn->error);
}

// Create personal_details table if not exists
$personal_details_sql = "CREATE TABLE IF NOT EXISTS personal_details (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(11) NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    guest_email VARCHAR(255) NOT NULL,
    guest_phone VARCHAR(15) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoice_data(id)
)";

if ($conn->query($personal_details_sql) !== TRUE) {
    error_log("Error creating personal_details table: " . $conn->error);
}

// Function to get invoice data by ID
function get_invoice_data_by_id($invoice_id) {
    global $conn;
    
    $sql = "SELECT * FROM invoice_data LEFT JOIN personal_details ON invoice_data.id = personal_details.invoice_id WHERE invoice_data.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

// Function to fetch all invoices
function fetch_all_invoices() {
    global $conn;

    $sql = "SELECT * FROM invoice_data LEFT JOIN personal_details ON invoice_data.id = personal_details.invoice_id";
    $result = $conn->query($sql);

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to soft delete an invoice
function soft_delete_invoice($invoice_id) {
    global $conn;

    // Check if the invoice_id is valid
    if (!is_numeric($invoice_id) || $invoice_id <= 0) {
        return "Invalid invoice ID.";
    }

    // Prepare the update query
    $sql = "UPDATE invoice_data SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Error preparing the statement: " . $conn->error);
        return "An error occurred. Please try again later.";
    }

    // Bind the invoice ID to the statement
    $stmt->bind_param("i", $invoice_id);

    // Execute the update
    if ($stmt->execute()) {
        $stmt->close();
        return "Invoice deleted successfully.";
    } else {
        error_log("Error deleting invoice: " . $stmt->error);
        $stmt->close();
        return "An error occurred. Please try again later.";
    }
}

// Close the connection
$conn->close();

?>
