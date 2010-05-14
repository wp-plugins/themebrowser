<?php
/*
Plugin Name: Theme Browser
Plugin URI: http://www.stillbreathing.co.uk/wordpress/themebrowser/
Description: Shows the list of installed themes in your site
Version: 0.2.1
Author: Chris Taylor
Author URI: http://www.stillbreathing.co.uk
*/

require_once( "plugin-register.class.php" );
$register = new Plugin_Register();
$register->file = __FILE__;
$register->slug = "themebrowser";
$register->name = "Theme Browser";
$register->version = "0.2.1";
$register->developer = "Chris Taylor";
$register->homepage = "http://www.stillbreathing.co.uk";

// setup shortcodes
// [themebrowser]
add_shortcode( 'themebrowser', 'themebrowser_shortcode' );

function themebrowser_shortcode() {
	// get all themes
	$themes = get_themes();
	$allowed_themes = "";
	// get allowed themes for WPMU
	if ( function_exists( "get_site_option" ) ) {
		$allowed_themes = get_site_option( 'allowedthemes' );
	}
	// loop themes
	foreach( $themes as $theme ) {
		// check the themes is allowed, and it has a screenshot
		if ( $theme["Screenshot"] != "" && ( $allowed_themes == "" || isset( $allowed_themes[ wp_specialchars( $theme['Stylesheet'] ) ] ) == true ) ) {
			// echo the details
			echo '
			<h3 style="clear:left;">' . $theme["Title"] . ' by ' . $theme["Author"] . '</h3>
			<p><img src="' . $theme["Theme Root URI"] . "/" . $theme["Template"] . "/" . $theme["Screenshot"] . '" alt="' . $theme["Title"] . '" style="float: left; margin: 0 1em 1em 0;" width="300px" /> ' . $theme["Description"] . '</p>
			';
		}
	}
}
?>