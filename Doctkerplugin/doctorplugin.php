<?php
/*
Plugin Name: WP Doctor Directory
Description: Imports doctors from CSV and allows searching and displaying them.
Version: 1.0
Author: Your Name
*/

// Registering Custom Post Type
function create_doctor_post_type() {
    register_post_type('doctors',
      array(
        'labels' => array(
          'name' => __('Doctors'),
          'singular_name' => __('Doctor')
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'custom-fields', 'thumbnail'),  // add 'thumbnail' here
      )
    );
  }
  add_action('init', 'create_doctor_post_type');
  
// add_action('init', 'create_doctor_post_type');
// Enqueueing your custom CSS
function doctor_directory_enqueue_assets() {
  // Enqueue Bootstrap
  wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
  // Enqueue custom styles
  wp_enqueue_style('my-plugin-custom', plugins_url('/style/styles.css', __FILE__));
  
  // Register and enqueue DataTables jQuery plugin
  wp_register_script('datatables', 'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js', array('jquery'), '1.11.3', true);
  wp_enqueue_script('datatables');
  
  // Register and enqueue DataTables CSS
  wp_register_style('datatables-css', 'https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css');
  wp_enqueue_style('datatables-css');

  // Inline script for DataTables initialization
  wp_add_inline_script('datatables', '
      jQuery(document).ready(function() {
        jQuery("#doctor-table").DataTable();
        jQuery("#search-table").DataTable();
      });
  ');
}
add_action('wp_enqueue_scripts', 'doctor_directory_enqueue_assets');


// Shortcode for search form
function doctor_search_form() {
  ob_start();
  ?>
  <form action="" method="get">
    <input type="text" name="doctor_search" value="<?php echo $_GET['doctor_search']; ?>" placeholder="Name or Specialization">
    <input type="submit" value="Search">
  </form>
  <?php
  return ob_get_clean();
}
add_shortcode('doctor_search', 'doctor_search_form');

// Search form handling
function doctor_search($query) {
  if($query->is_main_query() && !is_admin() && isset($_GET['doctor_search'])) {
    $meta_query = array(
      'relation' => 'OR',
      array(
        'key' => 'first_name',
        'value' => $_GET['doctor_search'],
        'compare' => 'LIKE'
      ),
      array(
        'key' => 'last_name',
        'value' => $_GET['doctor_search'],
        'compare' => 'LIKE'
      ),
      array(
        'key' => 'specialization',
        'value' => $_GET['doctor_search'],
        'compare' => 'LIKE'
      ),
    );

    $query->set('post_type', 'doctors');
    $query->set('meta_query', $meta_query);
  }
}
add_action('pre_get_posts', 'doctor_search');

// // Add admin menu page for CSV import
// function doctor_csv_import_menu() {
//   add_submenu_page('edit.php?post_type=doctors', 'Import CSV', 'Import CSV', 'manage_options', 'import_csv', 'import_csv');
// }
// add_action('admin_menu', 'doctor_csv_import_menu');

// // Admin page content
// function import_csv() {
//   if(isset($_FILES['csv'])) {
//     if($_FILES['csv']['type'] == 'text/csv') {
//       $handle = fopen($_FILES['csv']['tmp_name'], 'r');
      
//       // Loop through the CSV rows
//       while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
//         // Create a new doctor post
//         $post_id = wp_insert_post(array(
//           'post_title' => $data[0] . ' ' . $data[1],
//           'post_type' => 'doctors',
//           'post_status' => 'publish',
//           'meta_input' => array(
//             'first_name' => $data[0],
//             'last_name' => $data[1],
//             'specialization' => $data[2],
//             'office_address' => $data[3],
//             'available_time' => $data[4],
//             'contact_details' => $data[5],
//           ),
//         ));
//       }
      
//       fclose($handle);
//       echo 'Import completed successfully.';
//     } else {
//       echo 'Please upload a CSV file.';
//     }

// Add admin menu page for CSV import
function doctor_csv_import_menu() {
  add_submenu_page('edit.php?post_type=doctors', 'Import CSV', 'Import CSV', 'manage_options', 'import_csv', 'import_csv');
}
add_action('admin_menu', 'doctor_csv_import_menu');

// Admin page content
function import_csv() {
  if(isset($_FILES['csv'])) {
    if($_FILES['csv']['type'] == 'text/csv') {
      $handle = fopen($_FILES['csv']['tmp_name'], 'r');
      
      // Loop through the CSV rows
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Create a new doctor post
        $post_id = wp_insert_post(array(
          'post_title' => $data[0] . ' ' . $data[1],
          'post_type' => 'doctors',
          'post_status' => 'publish',
          'meta_input' => array(
            'first_name' => $data[0],
            'last_name' => $data[1],
            'specialization' => $data[2],
            'office_address' => $data[3],
            'available_time' => $data[4],
            'contact_details' => $data[5],
          ),
        ));
      }
      
      fclose($handle);
      echo '<div class="alert alert-success">Import completed successfully.</div>';
    } else {
      echo '<div class="alert alert-danger">Please upload a CSV file.</div>';
    }
  } else {
    ?>
    <form method="post" enctype="multipart/form-data" class="form-inline">
      <div class="form-group">
        <label for="csvFile" class="mr-2">Upload CSV:</label>
        <input type="file" name="csv" id="csvFile" class="form-control-file mr-2">
      </div>
      <input type="submit" value="Import CSV" class="btn btn-primary">
    </form>
    <?php
  }
}


// Shortcode for displaying all doctors
// Shortcode for displaying all doctors
// function list_all_doctors() {
//     $args = array(
//       'post_type' => 'doctors',
//       'posts_per_page' => -1,
//       'post_status' => 'publish'
//     );
  
//     $doctors = new WP_Query($args);
//     $output = '<ul>';
  
//     if($doctors->have_posts()) {
//       while($doctors->have_posts()) {
//         $doctors->the_post();
//         $output .= '<li>';
//         $output .= '<a href="'.get_permalink().'">'.get_the_title().'</a><br>';
//         $output .= 'Specialization: '.get_post_meta(get_the_ID(), 'specialization', true).'<br>';
//         $output .= 'Office Address: '.get_post_meta(get_the_ID(), 'office_address', true).'<br>';
//         $output .= 'Available Time: '.get_post_meta(get_the_ID(), 'available_time', true).'<br>';
//         $output .= 'Contact Details: '.get_post_meta(get_the_ID(), 'contact_details', true);
//         $output .= '</li>';
//       }
//       wp_reset_postdata();
//     } else {
//       $output .= '<li>No doctors found.</li>';
//     }
  
//     $output .= '</ul>';
  
//     return $output;
// }
function list_all_doctors() {
  $args = array(
      'post_type' => 'doctors',
      'posts_per_page' => -1,
      'post_status' => 'publish'
  );

  $doctors = new WP_Query($args);

  $output = '<table id="doctor-table" class="table table-hover table-striped alldoctable">';
  $output .= '<thead>
              <tr>
                <th scope="col">Doctor Name</th>
                <th scope="col">Specialization</th>
                <th scope="col">Office Address</th>
                <th scope="col">Available Time</th>
                <th scope="col">Contact Details</th>
              </tr>
              </thead>
              <tbody>';

  if($doctors->have_posts()) {
      while($doctors->have_posts()) {
          $doctors->the_post();
          $output .= '<tr>';
          $output .= '<td><a href="'.get_permalink().'">'.get_the_title().'</a></td>';
          $output .= '<td>'.get_post_meta(get_the_ID(), 'specialization', true).'</td>';
          $output .= '<td>'.get_post_meta(get_the_ID(), 'office_address', true).'</td>';
          $output .= '<td>'.get_post_meta(get_the_ID(), 'available_time', true).'</td>';
          $output .= '<td>'.get_post_meta(get_the_ID(), 'contact_details', true).'</td>';
          $output .= '</tr>';
      }
      wp_reset_postdata();
  } else {
      $output .= '<tr><td colspan="5">No doctors found.</td></tr>';
  }

  $output .= '</tbody></table>';

  return $output;
}
add_shortcode('list_all_doctors', 'list_all_doctors');

function my_plugin_get_doctor_template($single_template) {
  global $post;

  if ($post->post_type == 'doctors') {
       $single_template = dirname( __FILE__ ) . '/single-doctors.php';
  }
  return $single_template;
}
add_filter( 'single_template', 'my_plugin_get_doctor_template' );

// adding a filter to template_include
function doctorplugin_custom_search_template($template) {
  if (isset($_GET['doctor_search'])) {
    return plugin_dir_path(__FILE__) . 'search-doctors.php';
  }

  return $template;
}
add_filter('template_include', 'doctorplugin_custom_search_template');

?>
