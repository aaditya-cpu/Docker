<?php
/**
 * Plugin Name: Member Management Plugin
 * Description: Manage members with various designations and display them using shortcodes.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: mmp
 */

// Ensure direct file access is blocked.
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for easier access to plugin directories and features.
define('MMP_PATH', plugin_dir_path(__FILE__));
define('MMP_URL', plugin_dir_url(__FILE__));

// Include required files.
require MMP_PATH . 'includes/functions.php';
require MMP_PATH . 'includes/shortcode.php';
require MMP_PATH . 'includes/post-type.php';

if (is_admin()) {
    require MMP_PATH . 'admin/settings.php';
}

// Enqueue scripts and styles.
function mmp_enqueue_scripts() {
    // Enqueue Bootstrap CSS and JS.
    wp_enqueue_style('bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);

    // Enqueue custom styles and scripts if you have any.
    wp_enqueue_style('mmp-main-css', MMP_URL . 'assets/css/main.css');
    wp_enqueue_script('mmp-main-js', MMP_URL . 'assets/js/main.js', array('jquery'), null, true);
}

add_action('wp_enqueue_scripts', 'mmp_enqueue_scripts');

// Activation and deactivation hooks can be added if you have actions to perform during those events.
register_activation_hook(__FILE__, 'mmp_on_activation');
function mmp_on_activation() {
    // Actions to perform on plugin activation.
    // For example, you might want to flush rewrite rules if you've added custom post types or taxonomies.
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'mmp_on_deactivation');
function mmp_on_deactivation() {
    // Actions to perform on plugin deactivation.
    flush_rewrite_rules();
}

