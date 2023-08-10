<?php
function my_csv_importer_contacts_page() {
    // Query to get the imported contacts
    $query = new WP_Query([
        'post_type' => 'post', // Adjust as needed
    ]);

    echo '<div class="wrap">';
    echo '<h2>View Contacts</h2>';
    echo '<table class="table">';
    echo '<thead><tr><th>Name</th><th>Mobile</th><th>Email</th><th>Level</th></tr></thead>';
    echo '<tbody>';

    while ($query->have_posts()) {
        $query->the_post();
        echo '<tr>';
        echo '<td>' . get_the_title() . '</td>';
        echo '<td>' . get_post_meta(get_the_ID(), 'mobile', true) . '</td>';
        echo '<td>' . get_post_meta(get_the_ID(), 'email', true) . '</td>';
        echo '<td>' . get_post_meta(get_the_ID(), 'level', true) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

// Include contacts.php in main.php
include_once plugin_dir_path(__FILE__) . 'contacts.php';
