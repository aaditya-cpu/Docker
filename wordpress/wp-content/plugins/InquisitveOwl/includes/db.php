<?php
// Connect to the database
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "your_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    echo "Error creating invoice_data table: " . $conn->error;
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
    echo "Error creating personal_details table: " . $conn->error;
}
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
        return "Error preparing the statement: " . $conn->error;
    }

    // Bind the invoice ID to the statement
    $stmt->bind_param("i", $invoice_id);

    // Execute the update
    if ($stmt->execute()) {
        return "Invoice deleted successfully.";
    } else {
        return "Error deleting invoice: " . $stmt->error;
    }

    $stmt->close();
}

// You can call the soft_delete_invoice function here or in another file
?>

