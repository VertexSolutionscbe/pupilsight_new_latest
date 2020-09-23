<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package RLFG
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */

function enhanced_blocks_frontend_assets() { // phpcs:ignore
	// Styles.
	wp_enqueue_style(
		'enhanced_blocks-blocks-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		array(), // Dependency to include the CSS after it.
		ENHANCED_BLOCKS_VERSION // Version: File modification time.
	);

	wp_enqueue_style(
		'slick',
		plugins_url('src/assets/css/slick.css', dirname(__FILE__)),
		array(),
		filemtime(plugin_dir_path(__DIR__).'src/assets/css/slick.css')
	);

	wp_enqueue_style(
		'slick-theme',
		plugins_url('src/assets/css/slick-theme.css', dirname(__FILE__)),
		array(),
		filemtime(plugin_dir_path(__DIR__).'src/assets/css/slick-theme.css')
	);
	
	// Styles.
	wp_enqueue_style(
		'enhanced_blocks-eb-icons', // Handle.
		plugins_url( '/src/assets/css/eb-icons.css', dirname( __FILE__ ) ), // Block editor CSS.
		array(), // Dependency to include the CSS after it.
		ENHANCED_BLOCKS_VERSION // Version: File modification time.
	);

	wp_enqueue_script( 'imagesloaded');
	wp_enqueue_script('masonry');
	
	wp_enqueue_script(
		'slick',
		plugins_url('src/assets/js/slick.min.js', dirname(__FILE__)),
		array(),
		filemtime(plugin_dir_path(__DIR__).'src/assets/js/slick.min.js'),
		true
	);
}

// Hook: Frontend assets.
add_action( 'enqueue_block_assets', 'enhanced_blocks_frontend_assets' );

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function enhanced_blocks_editor_assets() { // phpcs:ignore
	// Scripts.
	wp_enqueue_script(
		'enhanced_blocks-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' , 'wp-editor'), // Dependencies, defined above.
		ENHANCED_BLOCKS_VERSION, // Version: File modification time.
		true // Enqueue the script in the footer.
	);
	
	
	// Styles.
	wp_enqueue_style(
		'enhanced_blocks-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		ENHANCED_BLOCKS_VERSION// Version: File modification time.
	);

	wp_localize_script( 'enhanced_blocks-block-js', 'enhanced_admin', array (
		'plugin' =>  plugin_dir_url(__FILE__),
		'ajax' => admin_url( 'admin-ajax.php' ),
	) );

	
}

// Hook: Editor assets.
add_action( 'enqueue_block_editor_assets', 'enhanced_blocks_editor_assets' );


function enhanced_blocks_loader_register_frontend_scripts()
{
	wp_enqueue_style(
		'twentytwenty-no-compass',
		plugins_url('src/assets/css/twentytwenty-no-compass.css',
			dirname(__FILE__)),
		array(),
		''
	);

	wp_enqueue_style(
		'twentytwenty-min',
		plugins_url('src/assets/css/twentytwenty.min.css', dirname(__FILE__)),
		array(),
		''
	);
	
	wp_enqueue_style(
		'owl-theme-default-min-css',
		plugins_url('src/assets/css/owl.theme.default.min.css', dirname(__FILE__))
	);

	wp_enqueue_style(
		'owl-carousel-min-css',
		plugins_url('src/assets/css/owl.carousel.min.css', dirname(__FILE__))
	);

	
	wp_enqueue_script(
		'jquery-twentytwenty-min-js',
		plugins_url('src/assets/js/jquery.twentytwenty.min.js', dirname(__FILE__)),
		array('jquery'),
		'547657',
		true
	);
	
	wp_enqueue_script(
		'jquery-event-move-min-js',
		plugins_url('src/assets/js/jquery.event.move.min.js', dirname(__FILE__)),
		array('jquery'),
		'547657',
		true
	);

	wp_enqueue_script(
		'owl-carousel-min-js',
		plugins_url('src/assets/js/owl.carousel.min.js', dirname(__FILE__)),
		array('jquery'),
		'547657',
		true
	);

	wp_enqueue_script('enhanced_blocks-custom-js',plugins_url('src/assets/js/enhanced-blocks.js', dirname(__FILE__)),array('jquery'),'54765799',true);

	wp_enqueue_style( 'font-awesome-min', plugins_url( '/assets/font-awesome/css/fontawesome.min.css' , __FILE__ ) );
	wp_enqueue_style( 'font-awesome-brands-min', plugins_url( '/assets/font-awesome/css/brands.min.css' , __FILE__ ) );
	wp_enqueue_style( 'font-awesome-solid-min', plugins_url( '/assets/font-awesome/css/solid.min.css' , __FILE__ ) );
	
}

add_action('wp_enqueue_scripts', 'enhanced_blocks_loader_register_frontend_scripts');

function enhanced_blocks_register_admin_scripts() {
	wp_enqueue_style( 'font-awesome', plugins_url( '/assets/font-awesome/css/fontawesome.min.css' , __FILE__ ) );
	wp_enqueue_style( 'font-awesome-brands', plugins_url( '/assets/font-awesome/css/brands.min.css' , __FILE__ ) );
	wp_enqueue_style( 'font-awesome-solid', plugins_url( '/assets/font-awesome/css/solid.min.css' , __FILE__ ) );
	wp_enqueue_style( 'react-icon-picker', plugins_url('/assets/css/react-icon-picker.min.css', __FILE__ ));
}

add_action('admin_enqueue_scripts', 'enhanced_blocks_register_admin_scripts');

// fetching the reusable block
function enhanced_get_saved_blocks() {
	$args = array(
		'post_type' => 'wp_block',
		'post_status' => 'publish'
	);
	$r = wp_parse_args( null, $args );
	$get_posts = new WP_Query;
	$wp_blocks = $get_posts->query($r);
	wp_send_json_success($wp_blocks);
}

add_action('wp_ajax_enhanced_get_saved_blocks', 'enhanced_get_saved_blocks');

// deleting the desired reusable block
function enhanced_delete_saved_block() {
	$block_id = (int) sanitize_text_field($_REQUEST['block_id']);
	$deleted_block = wp_delete_post($block_id);
	wp_send_json_success($deleted_block);
}

add_action("wp_ajax_enhanced_delete_saved_block", "enhanced_delete_saved_block");

// register a enhanced blocks custom category
add_filter('block_categories', function ($categories, $post) {
	return array_merge(
		array(
			array(
				'slug'  => 'enhanced-blocks',
				'title' => __('Enhanced Blocks', 'enhanced-blocks')
			),
		),
		$categories
	);

}, 1, 2);


if (!current_theme_supports('align-wide')) {
	function enhanced_blocks_plugin_setup() {
		add_theme_support( 'align-wide' );
	}
	add_action( 'after_setup_theme', 'enhanced_blocks_plugin_setup' );
}

if( !function_exists('enhanced_block_body_class') ){
	function enhanced_block_body_class($classes) {
		return $classes . ' enhanced-blocks-page';
	}
	add_filter('admin_body_class', 'enhanced_block_body_class');
}

if( !function_exists('enhanced_block_front_end_body_class') ){
	function enhanced_block_front_end_body_class($classes) {
		$classes[] = 'enhanced-blocks-page';
		return $classes;
	}
	add_filter('body_class', 'enhanced_block_front_end_body_class');
}