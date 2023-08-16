<?php

// Function to create the database table
function my_csv_importer_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'imported_contacts';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        mobile varchar(20) NOT NULL,
        email varchar(255) NOT NULL,
        level varchar(50) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function my_csv_importer_process_csv() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'imported_contacts';

    if ($_FILES['csv_file']['error'] == 0) {
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');

        // Skip header row
        fgetcsv($file);

        $importCount = 0; // Initialize import count

        while (($line = fgetcsv($file)) !== FALSE) {
            list($name, $mobile, $email, $level) = $line;

            // Check for duplicate mobile and email
            $existing_mobile = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE mobile = %s", $mobile));
            $existing_email = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE email = %s", $email));

            if (!$existing_mobile && !$existing_email) {
                $wpdb->insert($table_name, [
                    'name' => $name,
                    'mobile' => $mobile,
                    'email' => $email,
                    'level' => $level
                ]);

                $importCount++; // Increment import count
            }
        }

        fclose($file);

        // Log import results
        if ($importCount > 0) {
            error_log("CSV imported successfully. Count: $importCount");
        } else {
            error_log("CSV did not upload, please check count - 0");
        }
    }
}

function create_members_from_db() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'imported_contacts';

    $results = $wpdb->get_results("SELECT * FROM $table_name");

    foreach ($results as $row) {
        $post_data = array(
            'post_title'    => wp_strip_all_tags($row->name),
            'post_content'  => '', // Add content if required
            'post_status'   => 'publish',
            'post_author'   => 1, // Author ID
            'post_type'     => 'members',
            // Additional fields here
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id) {
            // Set the taxonomy (level) if needed
            wp_set_object_terms($post_id, $row->level, 'level', false);
            
            // Add custom fields if required
            add_post_meta($post_id, 'mobile', $row->mobile);
            add_post_meta($post_id, 'email', $row->email);
        }
    }
}

function my_csv_importer_import_page() {
    ?>
    <div class="container mt-5">
        <h1>CSV Import</h1>
        <p>Select a CSV file to import contacts:</p>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="process_csv">
            <div class="mb-3">
                <label for="csv_file" class="form-label">CSV File:</label>
                <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
    <?php
}

// Hook for processing CSV data
add_action('admin_post_process_csv', 'my_csv_importer_process_csv');
add_action('admin_post_nopriv_process_csv', 'my_csv_importer_process_csv');

?>
