<?php
/*
Plugin Name: WP-Thumbnail
Plugin URI: http://wp-thumbnail.robocasters.com
Description: WP-Thumbnail is a simple and neat Wordpress Plugin that not only allows you to use a short-code to generate dynamic screenshot of webpages in your posts but also takes a realtime snapshot of the served webpage and saves a link to it on the page itself that too without burdening the servers. This helps in optimizing search results on Google by adding a thumbnail to the left of the search result snippet and makes shared posts more attractive, with a relevant thumbnails, on Social Sites like Facebook.
Version: 1.1
Author: Arsh Shah Dilbagi (Robocasters)
Author URI: http://robocasters.com
License: GPL2
*/

if(preg_match("#^wp-thumbnail.php#", basename($_SERVER['PHP_SELF']))) exit();
$robo_dir = dirname(__FILE__) . '/';

function robo_current_URL() {
 	$pageURL = 'http';
 	if ($_SERVER["HTTPS"] == "on") {
 		$pageURL .= "s";
 	}
	$pageURL .= "://";
 	if ($_SERVER["SERVER_PORT"] != "80") {
  		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 	} else {
  		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 	}
 return $pageURL;
}

function robo_shot($url_action) {
	return 'http://s.wordpress.com/mshots/v1/' . urlencode(clean_url($url_action)) . '?w=200';
}

function robo_thumbnail_action() {
	echo '<meta name="thumbnail" content="' . robo_shot(robo_current_URL()) . '"/>';
	echo '<PageMap><DataObject type="thumbnail"><Attribute name="src" value="' . robo_shot(robo_current_URL()) . '"/><Attribute name="width" value="200"/><Attribute name="height" value="150"/></DataObject></PageMap>';
	echo '<div class="WP-Thumbnail" style="display:none;"><img src="' . robo_shot(robo_current_URL()) . '"></div>';
}

class robo_screenshot {   
    protected $apiUrl = 'http://s.wordpress.com/mshots/v1/';  
    public function __construct() {  
    	add_shortcode('roboshot', array($this, 'shortcode'));
    }  
    public function shortcode($atts, $content = NULL){
    	extract( shortcode_atts( array(
				'width' => 250,
    			'url' => 'http://robocasters.com',
    			'newpage' => TRUE,
                'link'    => TRUE,
    			'refresh' => TRUE,
    	), $atts ) );
    	$newpage = filter_var($newpage, FILTER_VALIDATE_BOOLEAN);
    	$link = filter_var($link, FILTER_VALIDATE_BOOLEAN);
    	$refresh = filter_var($refresh, FILTER_VALIDATE_BOOLEAN);
    	if ($refresh && $width==400) {
    		$width=401; 
    	}
    	$cssclasses = array ( 'roboshot' );
    	if ($refresh) {
	    	$cssclasses[] = 'roboshot_refresh';
			wp_register_script( 'roboshot_refresh', plugins_url( 'robo_screenshot.js' , __FILE__ ) ); 
   			wp_enqueue_script( 'roboshot_refresh', array('jquery'), '1.0.0', TRUE );
    	}
    	$imgUrl = $this->apiUrl . urlencode($url) .'?w='. $width;
    	$output = '';
    	if ($link == true) {
    		$output .= '<a href="'. $url .'" title="'. $url .'" class="roboshot_link" ';
    		if ($newpage == true)
    		{
    			$output .= ' target="_blank" ';
    		}
    		$output .= ' >';
    	}
		$output .= '<img class="'.implode(' ', $cssclasses).'" data-refreshcounter="0" data-width="'.$width.'" data-src="'.$imgUrl.'" src="'.$imgUrl.'" width="'.$width.'"/>';
		if ($link == true) {
			$output .= '</a>';
		}
		return $output;
    }  
} 

$_robo_screenshot = new robo_screenshot(); 

function robo_src_rel() {
	global $post;
	if ( !function_exists( 'has_post_thumbnail' ) )
		return;
	if ( !is_singular() or !has_post_thumbnail( $post->ID ) )
		return;
	$robo_thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
	echo '<link rel="image_src" href="' . esc_attr( $robo_thumb[0] ) . '" />';
	echo '<meta property="og:image" content="' . esc_attr( $robo_thumb[0] ) . '"/>';

}

function robo_settings_link($links) { 
  $settings_link = '<a href="admin.php?page=wp-thumbnail/wp-thumbnail_admin.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 

add_filter("plugin_action_links_$plugin", 'robo_settings_link' );
add_action( 'wp_head', 'robo_thumbnail_action' );
add_action( 'wp_head', 'robo_current_URL' );
add_action( 'wp_head', 'robo_src_rel' );

if (is_admin()) {
	require_once($robo_dir.'wp-thumbnail_admin.php');
}
?>