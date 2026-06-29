<?php
/**
 * Shown when a loop has no results.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="section section--flush">
	<p class="lede"><?php esc_html_e( 'Nothing here yet. Check back soon.', 'personal-site' ); ?></p>
	<?php if ( is_search() ) : ?>
		<div style="margin-top: var(--space-5); max-width: 28rem;">
			<?php get_search_form(); ?>
		</div>
	<?php endif; ?>
</div>
