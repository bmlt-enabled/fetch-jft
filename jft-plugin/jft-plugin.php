<?php
/*
Plugin Name: Just For Today Fetcher
Plugin URI: http://wordpress.org/extend/jft/
Author: Patrick J NERNA
Description: This is a plugin that fetches the Just For Today from NAWS and puts it on your site Simply add [jft] shortcode to your page.
Version: 1.0.1
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

function jft_func( $atts ){

	$d = new DOMDocument;

	$jft = new DOMDocument;
	
	// Get the contents of JFT
	$d->loadHTML(file_get_contents('https://jftna.org/jft/'));

	// Parse and extract just the body
	$body = $d->getElementsByTagName('body')->item(0);

	foreach ($body->childNodes as $child) {

		$jft->appendChild($jft->importNode($child, true));
	}
	// export just the html of body
	echo $jft->saveHTML();
}

// create [jft] shortcode
add_shortcode( 'jft', 'jft_func' );

?>