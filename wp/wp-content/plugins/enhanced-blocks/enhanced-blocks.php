<?php
/**
 * Plugin Name: Enhanced Blocks
 * Plugin URI: https://wordpress.org/plugins/enhanced-blocks/
 * Description: Enhanced Blocks - Page Builder blocks for Gutenberg Editor | Design responsive WordPress sites with few clicks | First ever Gutenberg block editor plugin for site building which is on the constant update.
 * Author: gutendev
 * Author URI: https://profiles.wordpress.org/gutendev
 * Version: 1.4.1
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: enhanced-blocks
 * 
 * @package RLFG
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define('ENHANCED_BLOCKS_VERSION', '1.4.1');
/**
 * Block Initializer.
 */

/**
 * Add a check before redirecting
 */
function enhanced_blocks_activate() {
	add_option( 'enhanced_blocks_redirect_on_activation', true );
}
register_activation_hook( __FILE__, 'enhanced_blocks_activate' );


if (!function_exists('enhanced_blocks_loader')) {
	function enhanced_blocks_loader()
	{
		require_once plugin_dir_path(__FILE__).'src/init.php';
		require_once plugin_dir_path(__FILE__).'src/post-grid.php';
		require_once plugin_dir_path(__FILE__).'src/social-share.php';
		require_once plugin_dir_path(__FILE__).'src/front-end-css.php';
		require_once plugin_dir_path(__FILE__).'admin/enhanced-welcome.php';
	}

	add_action('plugins_loaded', 'enhanced_blocks_loader');
}


if( !function_exists('enhanced_blocks_lang') ){
	function enhanced_blocks_lang(){
		load_plugin_textdomain( 'enhanced-blocks', false, basename(dirname(__FILE__) ) .'/languages');
	}
	add_action('init', 'enhanced_blocks_lang');
}

