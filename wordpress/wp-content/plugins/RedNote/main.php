<?php
/**
 * Plugin Name: My CSV Importer
 * Description: A plugin to import data from CSV and display it as post types.
 * Version: 1.0
 * Author: Your Name
 */

// Include other files
include_once plugin_dir_path(__FILE__) . 'importer.php';

// Activation hook to set up initial data
register_activation_hook(__FILE__, 'my_csv_importer_activation');
function my_csv_importer_activation() {
    my_csv_importer_create_table();
}

// Enqueue Bootstrap scripts and styles
function my_csv_importer_enqueue_scripts() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'my_csv_importer_enqueue_scripts');

// Function to add a top-level menu option for importing CSV
function my_add_csv_import_menu_option() {
    add_menu_page(
        'CSV Import',     // Page title
        'CSV Import',     // Menu title
        'manage_options', // Capability required to access
        'csv-import',     // Menu slug
        'my_csv_importer_import_page', // Callback function
        'dashicons-upload', // Menu icon
        25               // Menu position
    );
}
add_action('admin_menu', 'my_add_csv_import_menu_option');

// Function to render the import page
function my_csv_importer_import_page() {
    ?>
    <div class="wrap">
        <h2>CSV Import</h2>
        <form id="csv-import-form" method="post" enctype="multipart/form-data" class="form-inline">
            <input type="hidden" name="action" value="process_csv">
            <div class="form-group">
                <input type="file" name="csv_file" class="form-control-file" accept=".csv" required>
            </div>
            <input type="submit" value="Upload CSV" class="btn btn-primary">
        </form>
        <div id="import-progress"></div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('#csv-import-form').submit(function(event) {
                event.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: ajaxurl, // WordPress AJAX URL
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#import-progress').html('Importing... Please wait.');
                    },
                    success: function(response) {
                        $('#import-progress').html(response);
                    },
                    error: function() {
                        $('#import-progress').html('Error occurred during import.');
                    }
                });
            });
        });
    </script>
    <?php
}
?>
