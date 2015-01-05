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
		add_action( 'init', array( $this, 'register_content_type' ), 10 );
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
}
new WSU_Timeline();