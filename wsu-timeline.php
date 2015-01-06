<?php
/*
Plugin Name: WSU Timeline (125th)
Version: 0.0.1
Plugin URI: http://web.wsu.edu
Description: Provides the content requirements for the display of a timeline.
Author: washingtonstateuniversity, jeremyfelt
Author URI: http://web.wsu.edu
*/

class WSU_Timeline {
	/**
	 * @var string Current plugin version for cache breaking
	 */
	var $version = '0.0.1';

	/**
	 * @var string Slug used for the timeline point content type.
	 */
	var $point_content_type_slug = 'wsu-timeline-point';

	/**
	 * Setup plugin.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10 );
		add_action( 'init', array( $this, 'register_content_type' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	public function admin_enqueue_scripts() {
		if ( ! is_admin() || get_current_screen()->id !== $this->point_content_type_slug ) {
			return;
		}

		wp_enqueue_style( 'wsu-tp-admin-styles', plugins_url( '/css/style.css', __FILE__ ), array(), $this->version );
	}
	/**
	 * Register the content type be used to track points on a timeline.
	 */
	public function register_content_type() {
		$labels = array(
			'name'               => __( 'Timeline Points', 'wsuwp_uc' ),
			'singular_name'      => __( 'Timeline Point', 'wsuwp_uc' ),
			'all_items'          => __( 'All Timeline Points', 'wsuwp_uc' ),
			'add_new_item'       => __( 'Add Timeline Point', 'wsuwp_uc' ),
			'edit_item'          => __( 'Edit Timeline Point', 'wsuwp_uc' ),
			'new_item'           => __( 'New Timeline Point', 'wsuwp_uc' ),
			'view_item'          => __( 'View Timeline Point', 'wsuwp_uc' ),
			'search_items'       => __( 'Search Timeline Points', 'wsuwp_uc' ),
			'not_found'          => __( 'No Timeline Points found', 'wsuwp_uc' ),
			'not_found_in_trash' => __( 'No Timeline Points found in trash', 'wsuwp_uc' ),
		);

		$args = array(
			'labels' => $labels,
			'description' => 'Points on the WSU Timeline',
			'public' => true,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-location-alt',
			'supports' => array (
				'title',
				'editor',
				'revisions',
				'thumbnail',
			),
			'has_archive' => true,
		);
		register_post_type( $this->point_content_type_slug, $args );
	}

	/**
	 * Add meta boxes to be used with this content type.
	 *
	 * @param string $post_type The post type page being displayed.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( $this->point_content_type_slug !== $post_type ) {
			return;
		}

		add_meta_box( 'wsu-timeline-point-data', 'Timeline Point Data', array( $this, 'display_timeline_point_meta_box' ), null );
	}

	/**
	 * Display a meta box to capture the various data points required for a timeline point.
	 */
	public function display_timeline_point_meta_box( $post ) {
		$headline = get_post_meta( $post->ID, '_wsu_tp_headline', true );
		$sub_headline = get_post_meta( $post->ID, '_wsu_tp_sub_headline', true );
		$external_url = get_post_meta( $post->ID, '_wsu_tp_external_url', true );
		$submitter_name = get_post_meta( $post->ID, '_wsu_tp_submitter_name', true );
		$submitter_email = get_post_meta( $post->ID, '_wsu_tp_submitter_email', true );
		$submitter_phone = get_post_meta( $post->ID, '_wsu_tp_submitter_phone', true );
		$submitter_source = get_post_meta( $post->ID, '_wsu_tp_story_source', true );

		wp_nonce_field( 'wsu-timeline-save-point', '_wsu_timeline_point_nonce' );
		?>
		<div id="capture-point-data">
			<label for="wsu-tp-headline">Headline:</label>
			<input type="text" id="wsu-tp-headline" name="wsu_tp_headline" value="<?php echo esc_attr( $headline ); ?>" />

			<label for="wsu-tp-sub-headline">Sub headline:</label>
			<input type="text" id="wsu-tp-sub-headline" name="wsu_tp_sub_headline" value="<?php echo esc_attr( $sub_headline ); ?>" />

			<label for="wsu-tp-external-url">External URL:</label>
			<input type="text" id="wsu-tp-external-url" name="wsu_tp_external_url" value="<?php echo esc_attr( $external_url ); ?>" />

			<label for="wsu-tp-submitter-name">Submitter Name:</label>
			<input type="text" id="wsu-tp-submitter-name" name="wsu_tp_submitter_name" value="<?php echo esc_attr( $submitter_name ); ?>" />

			<label for="wsu-tp-submitter-email">Submitter Email:</label>
			<input type="text" id="wsu-tp-submitter-email" name="wsu_tp_submitter_email" value="<?php echo esc_attr( $submitter_email ); ?>" />

			<label for="wsu-tp-submitter-phone">Submitter Phone:</label>
			<input type="text" id="wsu-tp-submitter-phone" name="wsu_tp_submitter_phone" value="<?php echo esc_attr( $submitter_phone ); ?>" />

			<label for="wsu-tp-story-source">Story source notes:</label>
			<textarea id="wsu-tp-story-source" name="wsu_tp_story_source"><?php echo esc_textarea( $submitter_source ); ?></textarea>

			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Save all of the meta data associated with a timeline point when saved.
	 *
	 * @param int     $post_id ID of the post currently being saved.
	 * @param WP_Post $post    Object of the post being saved.
	 */
	public function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( $this->point_content_type_slug !== $post->post_type ) {
			return;
		}

		if ( ! isset( $_POST['_wsu_timeline_point_nonce'] ) || ! wp_verify_nonce( $_POST['_wsu_timeline_point_nonce'], 'wsu-timeline-save-point' ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( isset( $_POST['wsu_tp_headline'] ) && ! empty( trim( $_POST['wsu_tp_headline'] ) ) ) {
			update_post_meta( $post_id, '_wsu_tp_headline', sanitize_text_field( $_POST['wsu_tp_headline'] ) );
		} else {
			delete_post_meta( $post_id, '_wsu_tp_headline' );
		}

		if ( isset( $_POST['wsu_tp_sub_headline'] ) && ! empty( trim( $_POST['wsu_tp_sub_headline'] ) ) ) {
			update_post_meta( $post_id, '_wsu_tp_sub_headline', sanitize_text_field( $_POST['wsu_tp_sub_headline'] ) );
		} else {
			delete_post_meta( $post_id, '_wsu_tp_headline' );
		}

		if ( isset( $_POST['wsu_tp_external_url'] ) && ! empty( trim( $_POST['wsu_tp_external_url'] ) ) ) {
			update_post_meta( $post_id, '_wsu_tp_external_url', esc_url_raw( $_POST['wsu_tp_external_url'] ) );
		} else {
			delete_post_meta( $post_id, '_wsu_tp_external_url' );
		}

		if ( isset( $_POST['wsu_tp_submitter_name'] ) && ! empty( trim( $_POST['wsu_tp_submitter_name'] ) ) ) {
			update_post_meta( $post_id, '_wsu_tp_submitter_name', sanitize_text_field( $_POST['wsu_tp_submitter_name'] ) );
		} else {
			delete_post_meta( $post_id, '_wsu_tp_submitter_name' );
		}

		if ( isset( $_POST['wsu_tp_submitter_email'] ) && ! empty( trim( $_POST['wsu_tp_submitter_email'] ) ) ) {
			update_post_meta( $post_id, '_wsu_tp_submitter_email', sanitize_text_field( $_POST['wsu_tp_submitter_email'] ) );
		} else {
			delete_post_meta( $post_id, '_wsu_tp_submitter_email' );
		}

		if ( isset( $_POST['wsu_tp_submitter_phone'] ) && ! empty( trim( $_POST['wsu_tp_submitter_phone'] ) ) ) {
			update_post_meta( $post_id, '_wsu_tp_submitter_phone', sanitize_text_field( $_POST['wsu_tp_submitter_phone'] ) );
		} else {
			delete_post_meta( $post_id, '_wsu_tp_submitter_phone' );
		}

		if ( isset( $_POST['wsu_tp_story_source'] ) && ! empty( trim( $_POST['wsu_tp_story_source'] ) ) ) {
			update_post_meta( $post_id, '_wsu_tp_story_source', wp_kses_post( $_POST['wsu_tp_story_source'] ) );
		} else {
			delete_post_meta( $post_id, '_wsu_tp_story_source' );
		}

		return;
	}
}
new WSU_Timeline();