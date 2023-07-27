<?php
get_header(); ?>

<main id="main" class="site-main" role="main">

<?php
while ( have_posts() ) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <div class="card mb-3" style="--red-pantone: #e63946ff; --honeydew: #f1faeeff; --non-photo-blue: #a8dadcff; --cerulean: #457b9dff; --berkeley-blue: #1d3557ff;">
        <div class="row g-0">
            <div class="col-md-4">
                <?php if(has_post_thumbnail()): ?>
                    <div class="rounded-circle mx-auto d-block">
                        <?php the_post_thumbnail('full', ['class' => 'rounded-circle img-fluid']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <header class="entry-header">
                        <h1 class="card-title entry-title" style="color: var(--berkeley-blue);"><?php the_title(); ?></h1>
                    </header><!-- .entry-header -->
                    <div class="card-text entry-content">
                        <p><strong style="color: var(--cerulean);">First Name: </strong><span style="color: var(--red-pantone);"><?php echo get_post_meta(get_the_ID(), 'first_name', true); ?></span></p>
                        <p><strong style="color: var(--cerulean);">Last Name: </strong><span style="color: var(--red-pantone);"><?php echo get_post_meta(get_the_ID(), 'last_name', true); ?></span></p>
                        <p><strong style="color: var(--cerulean);">Specialization: </strong><span style="color: var(--red-pantone);"><?php echo get_post_meta(get_the_ID(), 'specialization', true); ?></span></p>
                        <p><strong style="color: var(--cerulean);">Office Address: </strong><span style="color: var(--red-pantone);"><?php echo get_post_meta(get_the_ID(), 'office_address', true); ?></span></p>
                        <p><strong style="color: var(--cerulean);">Available Time: </strong><span style="color: var(--red-pantone);"><?php echo get_post_meta(get_the_ID(), 'available_time', true); ?></span></p>
                        <p><strong style="color: var(--cerulean);">Contact Details: </strong><span style="color: var(--red-pantone);"><?php echo get_post_meta(get_the_ID(), 'contact_details', true); ?></span></p>
                    </div><!-- .entry-content -->
                </div>
            </div>
        </div>
    </div>

</article><!-- #post-## -->

<?php endwhile; // End of the loop. ?>

</main><!-- #main -->

<?php
get_sidebar();
get_footer();
?>
