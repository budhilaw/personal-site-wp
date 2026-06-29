<?php
/**
 * A blog post as an essay-style list row: text on the left, thumbnail on the
 * right. Used on the home "Recent writing" section and the archives.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_media = has_post_thumbnail();
?>
<article <?php post_class( $has_media ? 'post-row post-row--media' : 'post-row' ); ?>>
	<div class="post-row__body">
		<div class="meta">
			<?php personal_site_posted_on(); ?>
			<?php if ( has_category() ) : ?>
				<span class="meta__sep" aria-hidden="true">&middot;</span>
				<?php personal_site_primary_category(); ?>
			<?php endif; ?>
		</div>
		<h3 class="post-row__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="post-row__excerpt text-dim"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
		<?php
		$post_tags = get_the_tags();
		if ( $post_tags && ! is_wp_error( $post_tags ) ) :
			?>
			<div class="post-row__tags">
				<?php foreach ( array_slice( $post_tags, 0, 3 ) as $post_tag ) : ?>
					<a class="pill pill--sm" href="<?php echo esc_url( get_tag_link( $post_tag ) ); ?>">#<?php echo esc_html( $post_tag->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $has_media ) : ?>
		<a class="post-row__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'personal-site-card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
		</a>
	<?php endif; ?>
</article>
<?php
