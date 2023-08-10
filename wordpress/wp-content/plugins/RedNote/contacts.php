<?php
function my_csv_importer_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'imported_contacts';
    
    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            mobile varchar(15) NOT NULL,
            email varchar(100) NOT NULL,
            level varchar(50) NOT NULL,
            profile_picture_url varchar(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function my_csv_importer_contacts_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'imported_contacts';
    $contacts = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<div class="wrap">';
    echo '<h2>View Contacts</h2>';
    echo '<table class="table">';
    echo '<thead><tr><th>Name</th><th>Mobile</th><th>Email</th><th>Level</th><th>Profile Picture</th></tr></thead>';
    echo '<tbody>';

    foreach ($contacts as $contact) {
        echo '<tr>';
        echo '<td>' . $contact->name . '</td>';
        echo '<td>' . $contact->mobile . '</td>';
        echo '<td>' . $contact->email . '</td>';
        echo '<td>' . $contact->level . '</td>';
        echo '<td>' . $contact->profile_picture_url . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
include_once plugin_dir_path(__FILE__) . 'contacts.php';
?>


// Include contacts.php in main.php

