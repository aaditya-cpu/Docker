<?php
/**
 * Plugin Name: Members and CSV Importer
 * Description: A plugin to manage members with levels and import data from CSV.
 * Version: 1.0
 * Author: Death301
 */

// Register Custom Post Type Members
function create_members_cpt() {
  $labels = array(
    'name' => _x('Members', 'Post Type General Name', 'textdomain'),
    // ... more labels here ...
  );
  $args = array(
    'label' => __('Members', 'textdomain'),
    'labels' => $labels,
    'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields'),
    'taxonomies' => array('level'),
    'public' => true, // Make it publicly queryable
    'show_ui' => true, // Show in admin UI
    'show_in_menu' => true, // Show in admin sidebar
    'menu_position' => 5, // Position in menu
    'show_in_admin_bar' => true,
    'show_in_nav_menus' => true,
    'can_export' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'show_in_rest' => true,
    'capability_type' => 'post',
);
register_post_type('members', $args);

}
add_action('init', 'create_members_cpt', 0);

// Register Custom Taxonomy Level
function create_level_taxonomy() {
  $labels = array(
    'name' => _x('Levels', 'Taxonomy General Name', 'textdomain'),
    // ... more labels here ...
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => true,
    'public' => true,
    'show_ui' => true,
    'show_admin_column' => true,
    'show_in_nav_menus' => true,
    'show_tagcloud' => true,
  );
  register_taxonomy('level', array('members'), $args);
}
add_action('init', 'create_level_taxonomy', 0);

// Include other necessary files
include_once plugin_dir_path(__FILE__) . 'importer.php';

// Activation hook to set up initial data
register_activation_hook(__FILE__, 'my_csv_importer_activation');
function my_csv_importer_activation() {
    my_csv_importer_create_table();
    // my_csv_importer_process_csv(); // No need to call process_csv here; it will be triggered manually by the user
}


// Enqueue Bootstrap scripts and styles
function my_csv_importer_enqueue_scripts() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'my_csv_importer_enqueue_scripts');


function my_csv_importer_get_contacts() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'imported_contacts';
  $contacts = $wpdb->get_results("SELECT * FROM $table_name");

  foreach ($contacts as $contact) {
      echo '<p>' . $contact->name . ' - ' . $contact->email . '</p>';
  }
}


// Add a menu page for CSV import
function my_csv_importer_menu() {
    add_menu_page(
        'CSV Import',
        'CSV Import',
        'manage_options',
        'my-csv-importer',
        'my_csv_importer_import_page',
        'dashicons-upload',
        25
    );
}
add_action('admin_menu', 'my_csv_importer_menu');
?>
