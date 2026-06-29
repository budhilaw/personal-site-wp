<?php
/**
 * Home page. Built from the Personal Site blocks (Hero, Focus, Selected Work,
 * Recent Posts, CTA) placed on the front page in the block editor. Each block
 * renders its own full-width section, so the content is output directly.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
endif;

get_footer();
