<?php
/**
 * Template Name: About
 *
 * Portrait + intro with quick facts, the page content as prose, a career
 * timeline, freelance work, education, social links, and a closing call.
 * Most fields live under Theme Options -> About.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$portrait_id = (int) personal_site_opt( 'about', 'portrait_id', 0 );
	$facts       = array(
		__( 'Role', 'personal-site' )       => personal_site_opt( 'about', 'role', '' ),
		__( 'Based in', 'personal-site' )    => personal_site_opt( 'about', 'location', '' ),
		__( 'Focus', 'personal-site' )       => personal_site_opt( 'about', 'focus', '' ),
		__( 'Experience', 'personal-site' )  => personal_site_opt( 'about', 'experience', '' ),
	);
	$facts       = array_filter( $facts );
	$timeline    = personal_site_opt( 'about', 'timeline', array() );
	$freelance   = personal_site_opt( 'about', 'freelance', array() );
	$education   = personal_site_opt( 'about', 'education', '' );
	$email       = personal_site_opt( 'general', 'social_email', '' );
	?>
	<div class="container page-body">
		<section class="grid grid--about">
			<?php if ( $portrait_id ) : ?>
				<div class="about-portrait"><?php echo wp_get_attachment_image( $portrait_id, 'personal-site-card', false, array( 'alt' => get_the_title() ) ); ?></div>
			<?php elseif ( has_post_thumbnail() ) : ?>
				<div class="about-portrait"><?php the_post_thumbnail( 'personal-site-card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?></div>
			<?php endif; ?>

			<div>
				<p class="eyebrow"><?php esc_html_e( 'About', 'personal-site' ); ?></p>
				<h1 class="about-intro__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="about-intro__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<?php if ( $facts ) : ?>
					<dl class="facts">
						<?php foreach ( $facts as $label => $value ) : ?>
							<div class="fact">
								<dt class="eyebrow"><?php echo esc_html( $label ); ?></dt>
								<dd class="fact__value"><?php echo esc_html( $value ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
			</div>
		</section>

		<div class="prose"><?php the_content(); ?></div>

		<?php if ( ! empty( $timeline ) ) : ?>
			<section class="about-block">
				<p class="eyebrow"><?php echo esc_html( personal_site_opt( 'about', 'timeline_heading', __( 'Career history', 'personal-site' ) ) ); ?></p>
				<?php personal_site_render_timeline( $timeline ); ?>

				<?php if ( ! empty( $freelance ) ) : ?>
					<div class="about-sub">
						<p class="label text-faint"><?php echo esc_html( personal_site_opt( 'about', 'freelance_heading', __( 'Alongside — freelance', 'personal-site' ) ) ); ?></p>
						<?php personal_site_render_timeline( $freelance ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $education ) : ?>
					<div class="about-sub">
						<p class="label text-faint"><?php esc_html_e( 'Education', 'personal-site' ); ?></p>
						<p class="about-education"><?php echo esc_html( $education ); ?></p>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<section class="about-block">
			<p class="eyebrow"><?php esc_html_e( 'Connect', 'personal-site' ); ?></p>
			<?php personal_site_social_links( 'social about-connect' ); ?>
		</section>
	</div>

	<?php
	$cta_url = $email ? 'mailto:' . $email : '';
	if ( ! $cta_url ) {
		$contact = get_page_by_path( 'contact' );
		$cta_url = $contact ? get_permalink( $contact ) : '';
	}
	if ( $cta_url ) :
		?>
		<div class="section">
			<div class="container">
				<div class="section__head">
					<h2><?php esc_html_e( 'Want to work together?', 'personal-site' ); ?></h2>
					<a class="button" href="<?php echo esc_url( $cta_url ); ?>"><?php esc_html_e( 'Get in touch', 'personal-site' ); ?> <span aria-hidden="true">&rarr;</span></a>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
