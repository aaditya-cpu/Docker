<?php

function my_csv_importer_import_page() {
    ?>
    <div class="wrap">
        <h2>Import CSV</h2>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data" class="form-inline">
            <input type="hidden" name="action" value="process_csv">
            <div class="form-group">
                <input type="file" name="csv_file" class="form-control-file" accept=".csv" required>
            </div>
            <input type="submit" value="Upload CSV" class="btn btn-primary">
        </form>
    </div>
    <?php
}

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

add_action('admin_post_process_csv', 'my_csv_importer_process_csv');
add_action('admin_post_nopriv_process_csv', 'my_csv_importer_process_csv');

?>

