<?php
/*
Plugin Name: Theme Browser
Plugin URI: http://www.stillbreathing.co.uk/wordpress/themebrowser/
Description: Shows the list of installed themes in your site
Version: 0.5
Author: Chris Taylor
Author URI: http://www.stillbreathing.co.uk
*/

require_once( "plugin-register.class.php" );
$register = new Plugin_Register();
$register->file = __FILE__;
$register->slug = "themebrowser";
$register->name = "Theme Browser";
$register->version = "0.4";
$register->developer = "Chris Taylor";
$register->homepage = "http://www.stillbreathing.co.uk";

// setup shortcodes
// [themebrowser]
add_shortcode( 'themebrowser', 'themebrowser_shortcode' );

function themebrowser_shortcode( $atts ) {

	// get the number of thumbnails to show per page
	extract( shortcode_atts( array( 
		'perpage' => ''
	), $atts ) );
	
	// the number of thumbnails per page must be -1 for unlimited, or a positive number
	$perpage = (int) $perpage;
	if ( $perpage == 0 ) $perpage = -1;

	// set up pagination start
	$start = themebrowser_findstart( $perpage );
	$end = $start + $perpage;

	// get all themes
	$themes = wp_get_themes();
	$allowed_themes = "";
	// get allowed themes for WPMU
	if ( function_exists( "get_site_option" ) ) {
		$allowed_themes = get_site_option( 'allowedthemes' );
	} else {
		$allowed_themes = $themes;
	}
	
	// filter out disallowed themes
	$themestoshow = array();
	if ( $allowed_themes != "" ) {
		foreach( $themes as $theme ) {
			// check the themes is allowed, and it has a screenshot
			if ( $theme["Screenshot"] != "" && ( $allowed_themes == "" || isset( $allowed_themes[ wp_specialchars( $theme[ 'Stylesheet' ] ) ] ) == true ) ) {
				$themestoshow[] = $theme;
			}
		}
	} else {
		$themestoshow = array_values( $themes );
	}
	
	// get the total number of themes to show
	$total_themes = count( $themestoshow );
	
	// get the number of pages
	$pages = themebrowser_findpages( $total_themes, $perpage );
	
	// get the limit for this page
	$end = $start + $perpage;
	if ( $end > $total_themes ) $end = $total_themes;
	
	// if pagination isn't enabled
	if ( $perpage < 1 ) {
		$start = 0;
		$end = $total_themes;
	}
	
	// set up the pagination links
	$pagelist = paginate_links( array(
		'base' => add_query_arg( 'themebrowserpage', '%#%' ),
		'format' => '',
		'prev_text' => __('&laquo;'),
		'next_text' => __('&raquo;'),
		'total' => $pages,
		'current' => $_GET['themebrowserpage']
	));
	
	if ( $perpage > 0 && $pagelist != "" ) {
		echo '<p>Showing themes ' . ( $start + 1 ) . ' to ' . $end . ' (' . $perpage . ' per page).</p>';
		echo '<p style="clear:left;">' . $pagelist . '</p>';
	}
	
	// loop themes
	for( $x = $start; $x < $end; $x++  ) {
	
		// get this theme
		$theme = $themestoshow[ $x ];

		// echo the details
		echo '
		<h3 style="clear:left;">' . $theme[ "Title" ] . ' by ' . $theme[ "Author" ] . '</h3>
		<p><img src="' . $theme[ "Theme Root URI" ] . "/" . $theme[ "Template" ] . "/" . $theme[ "Screenshot" ] . '" alt="' . $theme[ "Title" ] . '" style="float: left; margin: 0 1em 1em 0;" width="300px" /> ' . $theme[ "Description" ] . '</p>
		';
	}
	
	if ( $perpage > 0 && $pagelist != "" ) {
		echo '<p style="clear:left;">' . $pagelist . '</p>';
	}
}

function themebrowser_findstart( $limit ) {
	if ( ( ! isset( $_GET[ 'themebrowserpage' ] ) ) || ( $_GET[ 'themebrowserpage' ] == "1" ) ) {
		$start = 0;
		$_GET[ 'themebrowserpage' ] = 1;
	} else {
		$start = ( $_GET[ 'themebrowserpage' ] -1 ) * $limit;
	}
	return $start;
}

function themebrowser_findpages( $count, $limit ) {
	 $pages = ( ( $count % $limit ) == 0 ) ? $count / $limit : floor( $count / $limit ) + 1; 
	 return $pages;
}

function themebrowser_pagelist( $curpage, $pages, $count, $limit ) {
	$qs = preg_replace( "&themebrowserpage=([0-9]+)", "", $_SERVER[ 'QUERY_STRING' ] );
	$start = themebrowser_findstart( $limit );
	$end = $start + $limit;
	$page_list  = "<span class=\"displaying-num\">Displaying " . ( $start + 1 ). "&#8211;" . $end . " of " . $count . "</span>\n"; 

	/* Print the first and previous page links if necessary */
	if ( ( $curpage != 1 ) && ( $curpage ) ) {
	   $page_list .= "<a href=\"" . $_SERVER[ 'PHP_SELF' ]."?" . $qs . "&amp;themebrowserpage=1\" class=\"page-numbers\">&laquo;</a>\n";
	} 

	if ( ( $curpage - 1 ) > 0 ) {
	   $page_list .= "<a href=\"" . $_SERVER[ 'PHP_SELF' ] . "?" . $qs . "&amp;themebrowserpage=" . ( $curpage - 1 ) . "\" class=\"page-numbers\">&lt;</a>\n";
	} 

	/* Print the numeric page list; make the current page unlinked and bold */
	for ( $i = 1; $i <= $pages; $i++ ) {
		if ( $i == $curpage ) {
			$page_list .= "<span class=\"page-numbers current\">" . $i . "</span>";
		} else {
			$page_list .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?" . $qs . "&amp;themebrowserpage=" . $i . "\" class=\"page-numbers\">" . $i . "</a>\n";
		}
		$page_list .= " ";
	  } 

	 /* Print the Next and Last page links if necessary */
	 if ( ( $curpage + 1 ) <= $pages ) {
		$page_list .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?" . $qs . "&amp;themebrowserpage=" . ( $curpage + 1 ) . "\" class=\"page-numbers\">&gt;</a>\n";
	 } 

	 if ( ( $curpage != $pages ) && ( $pages != 0 ) ) {
		$page_list .= "<a href=\"" . $_SERVER[ 'PHP_SELF' ] . "?" . $qs . "&amp;themebrowserpage=" . $pages . "\" class=\"page-numbers\">&raquo;</a>\n";
	 }
	 $page_list .= "\n"; 

	 return $page_list;
}
?>