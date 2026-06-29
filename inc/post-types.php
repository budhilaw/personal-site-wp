<?php
/**
 * Custom content: the Portfolio project type and its taxonomy.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Portfolio post type.
 */
function personal_site_register_portfolio() {
	$labels = array(
		'name'               => _x( 'Portfolio', 'post type general name', 'personal-site' ),
		'singular_name'      => _x( 'Project', 'post type singular name', 'personal-site' ),
		'menu_name'          => _x( 'Portfolio', 'admin menu', 'personal-site' ),
		'add_new'            => __( 'Add Project', 'personal-site' ),
		'add_new_item'       => __( 'Add New Project', 'personal-site' ),
		'edit_item'          => __( 'Edit Project', 'personal-site' ),
		'new_item'           => __( 'New Project', 'personal-site' ),
		'view_item'          => __( 'View Project', 'personal-site' ),
		'search_items'       => __( 'Search Projects', 'personal-site' ),
		'not_found'          => __( 'No projects found', 'personal-site' ),
		'not_found_in_trash' => __( 'No projects found in Trash', 'personal-site' ),
		'all_items'          => __( 'All Projects', 'personal-site' ),
	);

	register_post_type(
		'portfolio',
		array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 5,
			'rewrite'       => array( 'slug' => 'portfolio', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'personal_site_register_portfolio' );

/**
 * Register the Project Type taxonomy (e.g. Web, API, Tooling).
 */
function personal_site_register_project_type() {
	$labels = array(
		'name'              => _x( 'Project Types', 'taxonomy general name', 'personal-site' ),
		'singular_name'     => _x( 'Project Type', 'taxonomy singular name', 'personal-site' ),
		'search_items'      => __( 'Search Types', 'personal-site' ),
		'all_items'         => __( 'All Types', 'personal-site' ),
		'edit_item'         => __( 'Edit Type', 'personal-site' ),
		'update_item'       => __( 'Update Type', 'personal-site' ),
		'add_new_item'      => __( 'Add New Type', 'personal-site' ),
		'new_item_name'     => __( 'New Type Name', 'personal-site' ),
		'menu_name'         => __( 'Project Types', 'personal-site' ),
	);

	register_taxonomy(
		'project_type',
		'portfolio',
		array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'project-type', 'with_front' => false ),
		)
	);
}
add_action( 'init', 'personal_site_register_project_type' );

/**
 * Flush rewrite rules once on activation so the new slugs resolve.
 */
function personal_site_rewrite_flush() {
	personal_site_register_portfolio();
	personal_site_register_project_type();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'personal_site_rewrite_flush' );

/**
 * Optional "External URL" field for projects that link out (e.g. a live demo).
 *
 * @param WP_Post $post Current project.
 */
function personal_site_portfolio_meta_box() {
	add_meta_box(
		'personal_site_project_link',
		__( 'Project Links', 'personal-site' ),
		'personal_site_render_project_link_box',
		'portfolio',
		'side'
	);
}
add_action( 'add_meta_boxes', 'personal_site_portfolio_meta_box' );

/**
 * Render the project-link meta box.
 *
 * @param WP_Post $post Current project.
 */
function personal_site_render_project_link_box( $post ) {
	wp_nonce_field( 'personal_site_project_link', 'personal_site_project_link_nonce' );
	$live = get_post_meta( $post->ID, '_personal_site_project_url', true );
	$repo = get_post_meta( $post->ID, '_personal_site_repo_url', true );
	?>
	<p>
		<label for="ps-live-url"><strong><?php esc_html_e( 'Live URL', 'personal-site' ); ?></strong></label>
		<input id="ps-live-url" type="url" name="personal_site_project_url" value="<?php echo esc_attr( $live ); ?>" placeholder="https://" style="width:100%" />
	</p>
	<p>
		<label for="ps-repo-url"><strong><?php esc_html_e( 'Repository URL', 'personal-site' ); ?></strong></label>
		<input id="ps-repo-url" type="url" name="personal_site_repo_url" value="<?php echo esc_attr( $repo ); ?>" placeholder="https://github.com/&hellip;" style="width:100%" />
	</p>
	<p class="description"><?php esc_html_e( 'Shown as "View live project" and a source link on the project page. Leave a field blank to hide that link.', 'personal-site' ); ?></p>
	<?php
}

/**
 * Persist the project-link value.
 *
 * @param int $post_id Project ID.
 */
function personal_site_save_project_link( $post_id ) {
	if ( ! isset( $_POST['personal_site_project_link_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['personal_site_project_link_nonce'] ) ), 'personal_site_project_link' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$url = isset( $_POST['personal_site_project_url'] ) ? esc_url_raw( wp_unslash( $_POST['personal_site_project_url'] ) ) : '';
	update_post_meta( $post_id, '_personal_site_project_url', $url );

	$repo = isset( $_POST['personal_site_repo_url'] ) ? esc_url_raw( wp_unslash( $_POST['personal_site_repo_url'] ) ) : '';
	update_post_meta( $post_id, '_personal_site_repo_url', $repo );
}
add_action( 'save_post_portfolio', 'personal_site_save_project_link' );

/**
 * Gallery images for a project (stored as a comma-separated list of IDs).
 */
function personal_site_gallery_meta_box() {
	add_meta_box(
		'personal_site_gallery',
		__( 'Gallery', 'personal-site' ),
		'personal_site_render_gallery_box',
		'portfolio',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'personal_site_gallery_meta_box' );

/**
 * Read the saved gallery IDs for a project.
 *
 * @param int $post_id Project ID.
 * @return int[]
 */
function personal_site_gallery_ids( $post_id ) {
	$raw = get_post_meta( $post_id, '_personal_site_gallery', true );
	if ( ! $raw ) {
		return array();
	}
	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}

/**
 * Render the gallery picker.
 *
 * @param WP_Post $post Current project.
 */
function personal_site_render_gallery_box( $post ) {
	wp_nonce_field( 'personal_site_gallery', 'personal_site_gallery_nonce' );
	$ids = personal_site_gallery_ids( $post->ID );
	?>
	<div class="ps-gallery" data-ps-gallery>
		<div class="ps-gallery__items" data-ps-gallery-items>
			<?php foreach ( $ids as $id ) : ?>
				<?php $src = wp_get_attachment_image_url( $id, 'thumbnail' ); ?>
				<?php if ( $src ) : ?>
					<span class="ps-gallery__item" data-id="<?php echo esc_attr( $id ); ?>">
						<img src="<?php echo esc_url( $src ); ?>" alt="" />
						<button type="button" class="ps-gallery__remove" data-ps-gallery-remove aria-label="<?php esc_attr_e( 'Remove', 'personal-site' ); ?>">&times;</button>
					</span>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<input type="hidden" name="personal_site_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" data-ps-gallery-input />
		<p><button type="button" class="button" data-ps-gallery-add><?php esc_html_e( 'Add gallery images', 'personal-site' ); ?></button></p>
	</div>
	<style>
		.ps-gallery__items { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; }
		.ps-gallery__item { position: relative; width: 90px; height: 90px; border: 1px solid #dcdfe4; border-radius: 6px; overflow: hidden; }
		.ps-gallery__item img { width: 100%; height: 100%; object-fit: cover; display: block; }
		.ps-gallery__remove { position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; border: 0; border-radius: 4px; background: rgba(0,0,0,0.6); color: #fff; font-size: 14px; line-height: 1; cursor: pointer; }
	</style>
	<?php
}

/**
 * Persist the gallery IDs.
 *
 * @param int $post_id Project ID.
 */
function personal_site_save_gallery( $post_id ) {
	if ( ! isset( $_POST['personal_site_gallery_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['personal_site_gallery_nonce'] ) ), 'personal_site_gallery' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$raw = isset( $_POST['personal_site_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['personal_site_gallery'] ) ) : '';
	$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	update_post_meta( $post_id, '_personal_site_gallery', implode( ',', $ids ) );
}
add_action( 'save_post_portfolio', 'personal_site_save_gallery' );

/**
 * Enqueue the gallery picker script on the project editor.
 *
 * @param string $hook Current admin page.
 */
function personal_site_portfolio_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'portfolio' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	$rel = '/assets/js/admin-gallery.js';
	wp_enqueue_script( 'personal-site-gallery', PERSONAL_SITE_URI . $rel, array(), filemtime( PERSONAL_SITE_DIR . $rel ), true );
	wp_localize_script(
		'personal-site-gallery',
		'personalSiteGallery',
		array(
			'title'  => __( 'Select gallery images', 'personal-site' ),
			'button' => __( 'Add to gallery', 'personal-site' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'personal_site_portfolio_admin_assets' );
