<?php
/**
 * A portfolio project as an image-forward card: large image, then the title
 * with a "View" link. Used on the home "Selected work" section and the archive.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'work' ); ?>>
	<a class="work__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'personal-site-card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
		<?php else : ?>
			<span class="work__placeholder"></span>
		<?php endif; ?>
	</a>
	<div class="work__foot">
		<h3 class="work__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<a class="work__view" href="<?php the_permalink(); ?>">
			<?php esc_html_e( 'View', 'personal-site' ); ?> <span aria-hidden="true">&rarr;</span>
		</a>
	</div>
</article>
<?php
