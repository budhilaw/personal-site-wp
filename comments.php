<?php
/**
 * Comments and the comment form.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments__title">
			<?php
			$count = get_comments_number();
			printf(
				/* translators: %s: comment count. */
				esc_html( _n( '%s comment', '%s comments', $count, 'personal-site' ) ),
				esc_html( number_format_i18n( $count ) )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'avatar_size' => 40,
					'short_ping'  => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'class'     => 'pagination',
				'prev_text' => esc_html__( 'Older', 'personal-site' ),
				'next_text' => esc_html__( 'Newer', 'personal-site' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="text-dim"><?php esc_html_e( 'Comments are closed.', 'personal-site' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_form'         => 'comment-form stack',
			'title_reply'        => esc_html__( 'Leave a comment', 'personal-site' ),
			'title_reply_before' => '<h3 class="comments__reply-title">',
			'title_reply_after'  => '</h3>',
		)
	);
	?>
</section>
