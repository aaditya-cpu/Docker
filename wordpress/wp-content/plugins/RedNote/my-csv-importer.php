<?php
/**
 * Plugin Name: My CSV Importer
 * Description: A plugin to import data from CSV and display it as post types.
 * Version: 1.0
 * Author: Your Name
 */

// Include other files
include_once plugin_dir_path(__FILE__) . 'importer.php';
include_once plugin_dir_path(__FILE__) . 'shortcodes.php';
include_once plugin_dir_path(__FILE__) . 'filters.php';

// Enqueue Bootstrap scripts and styles
function my_csv_importer_enqueue_scripts() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'my_csv_importer_enqueue_scripts');

// Activation hook to set up initial data
register_activation_hook(__FILE__, 'my_csv_importer_activation');
function my_csv_importer_activation() {
  // You can set up default values or database tables here
}
// Add to the existing main.php file

// Function to add top-level menu
function my_csv_importer_admin_menu() {
    add_menu_page(
        'CSV Importer',
        'CSV Importer',
        'manage_options',
        'my_csv_importer',
        'my_csv_importer_import_page',
        'dashicons-upload',
        6
    );
    add_submenu_page(
        'my_csv_importer',
        'Import CSV',
        'Import CSV',
        'manage_options',
        'my_csv_importer_import',
        'my_csv_importer_import_page'
    );
    add_submenu_page(
        'my_csv_importer',
        'View Contacts',
        'View Contacts',
        'manage_options',
        'my_csv_importer_contacts',
        'my_csv_importer_contacts_page'
    );
}
add_action('admin_menu', 'my_csv_importer_admin_menu');

?>
