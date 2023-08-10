<?php
// Code for category-wise filtering based on the "level" field
function my_csv_importer_apply_filters($query) {
    if (!is_admin() && $query->is_main_query() && isset($_GET['level'])) {
        $query->set('meta_key', 'level');
        $query->set('meta_value', sanitize_text_field($_GET['level']));
    }
}

add_action('pre_get_posts', 'my_csv_importer_apply_filters');
?>
