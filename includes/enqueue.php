<?php

function rsnpp_artwork_enqueue_styles() {
	wp_enqueue_style( 'rsnpp', RSNPP_URL . '/assets/rsnpp.css', array(), RSNPP_VERSION );
}
add_action( 'wp_enqueue_scripts', 'rsnpp_artwork_enqueue_styles' );