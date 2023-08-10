<?php
add_action('wp_enqueue_scripts', 'my_csv_importer_enqueue_scripts');
function my_csv_importer_shortcode_display($atts) {
    $query = new WP_Query([
        'post_type' => 'post',
    ]);

    if ($query->have_posts()) {
        echo '<div class="container">';
        echo '<div class="row">';

        while ($query->have_posts()) {
            $query->the_post();
            $mobile = get_post_meta(get_the_ID(), 'mobile', true);
            $email = get_post_meta(get_the_ID(), 'email', true);
            $level = get_post_meta(get_the_ID(), 'level', true);
            $profile_picture_url = get_post_meta(get_the_ID(), 'profile_picture_url', true);

            echo '<div class="col-lg-4">';
            echo '<img src="' . esc_url($profile_picture_url) . '" class="rounded-circle" width="140" height="140" alt="Profile Picture">';
            echo '<h2>' . get_the_title() . '</h2>'; // Name
            echo '<p>Mobile: ' . esc_html($mobile) . '</p>';
            echo '<p>Email: ' . esc_html($email) . '</p>';
            echo '<p>Level: ' . esc_html($level) . '</p>';
            echo '<p><a class="btn btn-secondary" href="#">View details »</a></p>';
            echo '</div><!-- /.col-lg-4 -->';
        }

        echo '</div>'; // end row
        echo '</div>'; // end container
    }
}

add_shortcode('csv_display', 'my_csv_importer_shortcode_display');
