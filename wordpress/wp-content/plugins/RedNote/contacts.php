<?php
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

