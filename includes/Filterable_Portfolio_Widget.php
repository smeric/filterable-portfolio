<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Filterable_Portfolio_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'widget_filterable_portfolio',
			'description' => __('Display portfolio images with filtering.', 'filterable-portfolio'),
		);
		parent::__construct( 'widget_filterable_portfolio', __('Filterable Portfolio', 'filterable-portfolio'), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		extract($args);
		
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';

		echo $args['before_widget'];
	 
	    if ( ! empty( $title ) ) {
	        echo $args['before_title'] . $title . $args['after_title'];
	    }
	    
		echo do_shortcode('[filterable_portfolio]');
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance )
	{
		$title = ! empty( $instance['title'] ) ? esc_attr($instance['title']) : '';

		echo sprintf('<p><label for="%1$s">%2$s</label>', $this->get_field_id( 'title' ), __('Title (optional):', 'carousel-slider'));
		echo sprintf('<input type="text" class="widefat" id="%1$s" name="%2$s" value="%3$s" /></p>', $this->get_field_id( 'title' ), $this->get_field_name( 'title' ), $title);
		
		echo sprintf('<p><a target="_blank" href="'. admin_url('edit.php?post_type=portfolio&page=fp-settings') .'">%1$s</a> %2$s</p>',
				__('Click here', 'filterable-portfolio'),
				__('to change portfolio settings', 'filterable-portfolio')
			);
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		return $instance;
	}
}

add_action( 'widgets_init', function(){
	register_widget( 'Filterable_Portfolio_Widget' );
});