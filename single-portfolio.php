<?php
/**
 * Single portfolio project.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$project_url = get_post_meta( get_the_ID(), '_personal_site_project_url', true );
	$repo_url    = get_post_meta( get_the_ID(), '_personal_site_repo_url', true );
	$gallery_ids = personal_site_gallery_ids( get_the_ID() );
	$archive     = get_post_type_archive_link( 'portfolio' );
	$repo_label  = ( $repo_url && false !== stripos( $repo_url, 'github' ) ) ? __( 'GitHub', 'personal-site' ) : __( 'View source', 'personal-site' );
	?>
	<article <?php post_class(); ?>>
		<div class="container">
			<header class="page-header">
				<p class="eyebrow">
					<a class="back-eyebrow" href="<?php echo esc_url( $archive ); ?>">&larr; <?php esc_html_e( 'Work', 'personal-site' ); ?></a>
				</p>
				<h1><?php the_title(); ?></h1>
				<?php if ( $project_url || $repo_url ) : ?>
					<p class="page-header__links">
						<?php if ( $project_url ) : ?>
							<a class="view-live" href="<?php echo esc_url( $project_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'View live project', 'personal-site' ); ?> <span aria-hidden="true">&#8599;</span>
							</a>
						<?php endif; ?>
						<?php if ( $repo_url ) : ?>
							<a class="view-live" href="<?php echo esc_url( $repo_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $repo_label ); ?> <span aria-hidden="true">&#8599;</span>
							</a>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</header>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="section">
				<div class="container">
					<figure class="project-cover"><?php the_post_thumbnail( 'personal-site-cover' ); ?></figure>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="section">
				<div class="container">
					<div class="prose"><?php the_content(); ?></div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $gallery_ids ) : ?>
			<div class="section">
				<div class="container">
					<h2 class="gallery__heading"><?php esc_html_e( 'Gallery', 'personal-site' ); ?></h2>
					<div class="gallery-grid">
						<?php
						$index = 1;
						foreach ( $gallery_ids as $gid ) :
							$caption = wp_get_attachment_caption( $gid );
							$caption = $caption ? $caption : sprintf(
								/* translators: %d: image number. */
								__( 'Gallery image %d', 'personal-site' ),
								$index
							);
							?>
							<figure class="gallery-item">
								<?php echo wp_get_attachment_image( $gid, 'personal-site-card', false, array( 'alt' => $caption ) ); ?>
								<figcaption class="gallery-item__cap"><?php echo esc_html( $caption ); ?></figcaption>
							</figure>
							<?php
							++$index;
						endforeach;
						?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
