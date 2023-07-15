<?php
/*
 * Template Name: Payment Confirmation Template
 * Description: This is a custom template for payment confirmation.
 */

get_header(); // Include the theme header
?>

<main id="primary" class="site-main">
    <div id="content" class="container">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?>xcfbxdfb</h1>
            </header>

            <div class="entry-content">
                <?php
                // Your custom payment confirmation logic and content here
                ?>
            </div>
        </article>
    </div>
</main>

<?php
get_footer(); // Include the theme footer
?>
