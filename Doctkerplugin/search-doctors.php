<?php
/**
 * The template for displaying doctor search results
 */
get_header();
?>

<div id="primary" class="content-area">
  <main id="main" class="site-main">
    <?php
    if (have_posts()) { ?>

      <header class="page-header">
        <h1 class="page-title">
          <?php
          /* translators: Search query. */
          printf(esc_html__('Search Results for: %s', 'textdomain'), '<span>' . get_search_query() . '</span>');
          ?>
        </h1>
      </header><!-- .page-header -->

      <!-- Bootstrap table -->
      <table id="search-results" class="table table-hover table-dark search-table">
        <thead>
          <tr>
            <th scope="col">Name</th>
            <th scope="col">Specialization</th>
            <th scope="col">Office Address</th>
            <th scope="col">Available Time</th>
            <th scope="col">Contact Details</th>
          </tr>
        </thead>
        <tbody>

          <?php
          /* Start the Loop */
          while (have_posts()) {
            the_post();

            // Get post meta
            $specialization = get_post_meta(get_the_ID(), 'specialization', true);
            $office_address = get_post_meta(get_the_ID(), 'office_address', true);
            $available_time = get_post_meta(get_the_ID(), 'available_time', true);
            $contact_details = get_post_meta(get_the_ID(), 'contact_details', true);
            ?>

            <tr>
              <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
              <td><?php echo $specialization; ?></td>
              <td><?php echo $office_address; ?></td>
              <td><?php echo $available_time; ?></td>
              <td><?php echo $contact_details; ?></td>
            </tr>

            <?php
          }
          ?>

        </tbody>
      </table>
      <!-- End Bootstrap table -->

      <?php
      the_posts_navigation();

    } else {

      get_template_part('template-parts/content', 'none');

    }
    ?>
  </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
