<?php
/*
Plugin Name: RS Next and Previous Posts
Description: Adds next and previous posts links with thumbnails, titles, and excerpts at the end of your post content.
Author: Radley Sustaire
Author URI: https://radleysustaire.com/
Version: 1.0.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

define( 'RSNPP_VERSION', '1.0.4' );
define( 'RSNPP_PATH', plugin_dir_path(__FILE__) );
define( 'RSNPP_URL', plugin_dir_url(__FILE__) );

function rsnpp_initialize() {
	include( 'includes/enqueue.php' );
	include( 'includes/post-nav.php' );
}
add_action( 'plugins_loaded', 'rsnpp_initialize' );