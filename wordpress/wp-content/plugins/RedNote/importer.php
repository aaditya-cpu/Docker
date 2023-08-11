<?php
// Function to process the imported CSV data
function my_csv_importer_process_csv() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'imported_contacts';

    if ($_FILES['csv_file']['error'] == 0) {
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');

        // Skip header row
        fgetcsv($file);

        while (($line = fgetcsv($file)) !== FALSE) {
            list($name, $mobile, $email, $level, $profile_picture_url) = $line;

            // Check for duplicate mobile and email
            $existing_mobile = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE mobile = %s", $mobile));
            $existing_email = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE email = %s", $email));

            if (!$existing_mobile && !$existing_email) {
                $wpdb->insert($table_name, [
                    'name' => $name,
                    'mobile' => $mobile,
                    'email' => $email,
                    'level' => $level,
                    'profile_picture_url' => $profile_picture_url
                ]);

                // Create a new post for each line
                $post_id = wp_insert_post([
                    'post_title'    => $name,
                    'post_content'  => '', // Add content if needed
                    'post_status'   => 'publish',
                    'post_type'     => 'post',
                ]);

                // Add custom fields
                update_post_meta($post_id, 'mobile', $mobile);
                update_post_meta($post_id, 'email', $email);
                update_post_meta($post_id, 'level', $level);
                
                // Attach profile picture
                media_sideload_image($profile_picture_url, $post_id, $name);
            }
        }

        fclose($file);
        // Redirect to a confirmation page or display a success message
    }
}

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
        profile_picture_url varchar(255),
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Hook for processing CSV data
add_action('admin_post_process_csv', 'my_csv_importer_process_csv');
add_action('admin_post_nopriv_process_csv', 'my_csv_importer_process_csv');
?>
