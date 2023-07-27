<?php get_header(); ?>

<main id="main" class="site-main" role="main">

    <?php while ( have_posts() ) : the_post(); ?>

        <div class="card mb-3">
            <div class="row g-0">
                <div class="col-md-4 d-flex align-items-center justify-content-center">
                    <?php 
                        if ( has_post_thumbnail() ) { 
                            the_post_thumbnail('medium', array('class' => 'rounded-circle img-fluid'));
                        } 
                    ?>
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title"><?php the_title(); ?></h5>
                        <p class="card-text"><strong>First Name: </strong><?php echo get_post_meta(get_the_ID(), 'first_name', true); ?></p>
                        <p class="card-text"><strong>Last Name: </strong><?php echo get_post_meta(get_the_ID(), 'last_name', true); ?></p>
                        <p class="card-text"><strong>Specialization: </strong><?php echo get_post_meta(get_the_ID(), 'specialization', true); ?></p>
                        <p class="card-text"><strong>Office Address: </strong><?php echo get_post_meta(get_the_ID(), 'office_address', true); ?></p>
                        <p class="card-text"><strong>Available Time: </strong><?php echo get_post_meta(get_the_ID(), 'available_time', true); ?></p>
                        <p class="card-text"><strong>Contact Details: </strong><?php echo get_post_meta(get_the_ID(), 'contact_details', true); ?></p>
                    </div>
                </div>
            </div>
        </div>

    <?php endwhile; // End of the loop. ?>

</main><!-- #main -->

<?php
get_sidebar();
get_footer();
?>
