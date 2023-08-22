<?php
/*
 * Plugin Name:       HyperPress
 * Plugin URI:        https://andreasjr.com/projects/hyperpress
 * Description:       A drop in solution to provide HTMX technologies for Gutenberg and Block Based themes.
 * Version:           0.1.1
 * Requires at least: 7.2
 * Requires PHP:      7.2
 * Author:            Andreas Reif
 * Author URI:        https://andreasjr.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hyperpress
 * Domain Path:       /languages
 */

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'htmx', plugin_dir_url( __FILE__  ) . 'assets/htmx.min.js', array(), '1.9.2', false );
	wp_enqueue_script( 'htmx-ext-head-support', 'https://unpkg.com/htmx.org/dist/ext/head-support.js', array('htmx'), '1.9.2', false );
	wp_enqueue_script( 'htmx-custom', plugin_dir_url( __FILE__  ) . 'assets/custom.js', array('htmx', 'htmx-ext-head-support'), '0.0.1', true );
}, 1, 1 );

define('HTMX_BLOG_URL', get_bloginfo('wpurl'));

// add_filter( 'the_content', function($htm) {
// 	echo '<pre>';
// 	print_r(esc_html__($htm));
// 	echo '</pre>';
// }, 1 );

// ob_start();

// add_action('shutdown', function() {
//     $final = '';

//     // We'll need to get the number of ob levels we're in, so that we can iterate over each, collecting
//     // that buffer's output into the final output.
//     $levels = ob_get_level();
	
//     for ($i = 0; $i < $levels; $i++) {
// 		$final .= ob_get_clean();
//     }
// 	echo $levels;

//     // Apply any filters to the final output
//     echo apply_filters('htmx_output', $final);
// }, 0);

function htmx_update_urls( $block_content ) {
	$c = new WP_HTML_Tag_Processor( $block_content );
	$blog_url = get_bloginfo('wpurl');

	while( $c->next_tag(array( 'tag_name' => 'a' )) ) {
		$href = $c->get_attribute('href');
		$htmx = $c->get_attribute('hx-get');

		$is_root_URL = strpos($href, '/') == 0;

		if (
			str_contains( $href, $blog_url ) ||
			$is_root_URL
		) {
			if ($htmx) continue;
		}
		else continue;
		// $c->set_attribute('href', "#");

		if ($is_root_URL) {
			$href = $blog_url . $href;
			$c->set_attribute('href', $href);
		}

		$c->set_attribute('hx-get', $href);
		$c->set_attribute('hx-target', 'body');
		$c->set_attribute('hx-push-url', 'true');
		$c->set_attribute('hx-swap', 'innerHTML show:top transition:true');

	};
	
	$new_block_content = $c->get_updated_html();

	return $new_block_content;
}

add_filter( 'render_block', function( $block_content, $block ) {
	if (
		$block['blockName'] == 'core/navigation' ||
		$block['blockName'] == 'core/site-title' ||
		$block['blockName'] == 'core/paragraph' ||
		$block['blockName'] == 'core/post-terms' ||
		$block['blockName'] == 'core/post-author' ||
		$block['blockName'] == 'core/post-featured-image' ||
		$block['blockName'] == 'core/image' ||
		$block['blockName'] == 'core/query'
	) $block_content = htmx_update_urls( $block_content );
	return $block_content;
}, 999, 2);

add_filter( 'the_content', function( $content ) {
	// echo esc_html( $content );
	$content = htmx_update_urls( $content );
	// echo '<h1>new</h1>';
	// echo esc_html( $content );
	return $content;
}, 999, 1);

add_filter('htmx_output', function($output) {
    return str_replace('Latest', 'earliest', $output);
});

// add_filter( 'body_class', function ( $classes ) {
//     $classes[] = '" hx-ext="head-support';

//     return $classes;
// }, 999 );


// TODO
// add support for dynamic comment posting
// add loading indicator