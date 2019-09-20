<?php

if ( ! defined( 'ABSPATH' ) ) {
	die; // If this file is called directly, abort.
}

class Filterable_Portfolio_Helper {

	/**
	 * Post type
	 */
	const POST_TYPE = 'portfolio';

	/**
	 * Portfolio category
	 */
	const CATEGORY = 'portfolio_cat';

	/**
	 * Portfolio skill
	 */
	const SKILL = 'portfolio_skill';

	/**
	 * Get all portfolios
	 *
	 * @param array $args
	 *
	 * @return WP_Post[]
	 */
	public static function get_portfolios( $args = [] ) {
		$options        = get_option( 'filterable_portfolio' );
		$posts_per_page = isset( $options['per_page'] ) ? intval( $options['per_page'] ) : - 1;
		$orderby        = isset( $options['orderby'] ) ? esc_attr( $options['orderby'] ) : 'ID';
		$order          = isset( $options['order'] ) ? esc_attr( $options['order'] ) : 'DESC';

		$portfolios_args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
			'orderby'        => $orderby,
			'order'          => $order,
		);

		if ( isset( $args['per_page'] ) && is_numeric( $args['per_page'] ) ) {
			$portfolios_args['posts_per_page'] = intval( $args['per_page'] );
		}

		if ( isset( $args['page'] ) && is_numeric( $args['page'] ) ) {
			$portfolios_args['paged'] = intval( $args['page'] );
		}

		if ( isset( $args['featured'] ) && $args['featured'] == true ) {
			$portfolios_args['meta_query'] = array(
				array(
					'key'   => '_is_featured_project',
					'value' => 'yes',
				)
			);
		}

		return get_posts( $portfolios_args );
	}

	/**
	 * Get related portfolios
	 *
	 * @param int|\WP_Post|null $post Post ID or post object. Defaults to global $post.
	 *
	 * @return \WP_Post[] List of posts.
	 */
	public static function get_related_portfolios( $post = null ) {
		$post       = get_post( $post );
		$categories = get_the_terms( $post->ID, self::CATEGORY );
		$skills     = get_the_terms( $post->ID, self::SKILL );

		$options  = get_option( 'filterable_portfolio' );
		$per_page = isset( $options['related_projects_number'] ) ? intval( $options['related_projects_number'] ) : 3;

		$args = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => $per_page,
			'post__not_in'   => array( $post->ID ),
			'tax_query'      => array( 'relation' => 'OR' )
		);

		if ( is_array( $categories ) ) {
			$category_ids = wp_list_pluck( $categories, 'term_id' );

			$args['tax_query'][] = array( 'taxonomy' => self::CATEGORY, 'field' => 'id', 'terms' => $category_ids );
		}

		if ( is_array( $skills ) ) {
			$skill_ids = wp_list_pluck( $skills, 'term_id' );

			$args['tax_query'][] = array( 'taxonomy' => self::SKILL, 'field' => 'id', 'terms' => $skill_ids );
		}

		return get_posts( $args );
	}

	/**
	 * Get portfolio images ids
	 *
	 * @param int|\WP_Post|null $post Post ID or post object. Defaults to global $post.
	 *
	 * @return array
	 */
	public static function get_portfolio_images_ids( $post = null ) {
		$post           = get_post( $post );
		$project_images = get_post_meta( $post->ID, '_project_images', true );
		if ( is_string( $project_images ) ) {
			// Remove last comma if any
			$project_images = rtrim( $project_images, ',' );
			// Split by comma
			$project_images = explode( ',', $project_images );
			// Remove empty value from array
			$project_images = array_filter( $project_images );
		}

		return is_array( $project_images ) ? $project_images : array();
	}

	/**
	 * Check if has portfolio images
	 *
	 * @param int|\WP_Post|null $post Post ID or post object. Defaults to global $post.
	 *
	 * @return bool
	 */
	public static function has_portfolio_images( $post = null ) {
		$ids = self::get_portfolio_images_ids( $post );

		return count( $ids ) > 0;
	}

	/**
	 * Get categories from portfolios
	 *
	 * @param WP_Post[] $portfolios
	 *
	 * @return array|WP_Term[]
	 */
	public static function get_categories_from_portfolios( array $portfolios ) {
		$ids = wp_list_pluck( $portfolios, "ID" );

		return wp_get_object_terms( $ids, self::CATEGORY );
	}

	/**
	 * Get portfolio categories
	 *
	 * @return array|WP_Term[]
	 */
	public static function get_portfolio_categories() {
		$terms = get_terms( array(
			'taxonomy'   => self::CATEGORY,
			'hide_empty' => true,
		) );

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		return count( $terms ) ? $terms : array();
	}

	/**
	 * Get portfolio skills
	 *
	 * @return array|\WP_Term[]
	 */
	public static function get_portfolio_skills() {
		$terms = get_terms( array(
			'taxonomy'   => self::SKILL,
			'hide_empty' => true,
		) );

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		return count( $terms ) ? $terms : array();
	}

	/**
	 * Check if single portfolio page
	 *
	 * @return bool
	 */
	public static function is_single_portfolio() {
		return is_singular( self::POST_TYPE );
	}

	/**
	 * Check if portfolio archive page
	 *
	 * @return bool
	 */
	public static function is_portfolio_archive() {
		if ( is_post_type_archive( self::POST_TYPE ) ) {
			return true;
		}
		if ( is_tax( self::CATEGORY ) || is_tax( self::SKILL ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check current theme has single-portfolio.php file
	 *
	 * @return bool
	 */
	public static function has_single_template() {
		$template = locate_template( 'single-portfolio.php' );
		if ( '' != $template ) {
			return true;
		}

		return false;
	}

	/**
	 * Check current theme has custom archive template
	 *
	 * @return bool
	 */
	public static function has_archive_template() {
		$templates = array(
			'archive-portfolio.php',
			'taxonomy-portfolio_cat.php',
			'taxonomy-portfolio_skill.php'
		);

		if ( '' != locate_template( $templates ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if Shapla theme or it's child theme is active
	 *
	 * @return boolean
	 */
	public static function is_shapla_theme_activate() {
		$current_theme  = wp_get_theme();
		$theme_name     = $current_theme->get( 'Name' );
		$theme_template = $current_theme->get( 'Template' );

		if ( $theme_template == 'shapla' || $theme_name == 'Shapla' ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if skills and categories should show with link
	 * If current theme support archive template then link should include
	 *
	 * @return bool
	 */
	public static function support_archive_template() {
		return ( self::has_archive_template() || self::is_shapla_theme_activate() );
	}
}
