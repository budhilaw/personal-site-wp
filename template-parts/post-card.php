<?php
/**
 * A blog post rendered as a media card (used in grids).
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'personal-site-card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
		</a>
	<?php endif; ?>

	<div class="card__body">
		<?php personal_site_primary_category(); ?>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<div class="card__footer meta">
			<?php personal_site_posted_on(); ?>
			<span class="meta__sep" aria-hidden="true">&middot;</span>
			<span><?php echo esc_html( personal_site_reading_time() ); ?></span>
		</div>
	</div>
</article>
