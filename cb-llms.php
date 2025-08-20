<?php
/**
 * Plugin Name: CBP LLMS Exporter
 * Description: Export eligible posts/pages in markdown syntax to llms.txt
 * Version: 1.0
 * Author: Chillibyte - DS
 * Author URI: https://chillibyte.co.uk
 *
 * @package CBP LLMS Exporter
 */

// Add admin menu.
add_action( 'admin_menu', 'cbp_llms_add_admin_menu' );

/**
 * Adds the LLMS Export page to the WordPress admin menu.
 */
function cbp_llms_add_admin_menu() {
	add_submenu_page(
		'tools.php',
		'LLMS Export',
		'LLMS Export',
		'manage_options',
		'cbp-llms-export',
		'cbp_llms_admin_page'
	);
}

/**
 * Outputs the HTML for the LLMS Export admin page.
 */
function cbp_llms_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$post_types = get_post_types( array( 'public' => true ), 'objects' );
	// Load saved selections and summary.
	$selected = get_option( 'cbp_llms_selected_post_types', array_keys( $post_types ) );
	$summary  = get_option( 'cbp_llms_summary', '' );
	echo '<div class="wrap"><h1>LLMS Export</h1>';
	echo '<form method="post">';
	wp_nonce_field( 'cbp_llms_export_action', 'cbp_llms_export_nonce' );
	echo '<h2>Site Summary</h2>';
	echo '<textarea name="cbp_llms_summary" rows="2" style="width:100%;max-width:600px;" placeholder="Enter a one-line summary for this site.">' . esc_textarea( $summary ) . '</textarea>';
	echo '<h2>Select Post Types</h2>';
	foreach ( $post_types as $pt => $obj ) {
		$checked = in_array( $pt, $selected, true ) ? 'checked' : '';
		echo '<label><input type="checkbox" name="cbp_llms_post_types[]" value="' . esc_attr( $pt ) . '" ' . esc_attr( $checked ) . '> ' . esc_html( $obj->labels->name ) . '</label><br>';
	}
	submit_button( 'Export to llms.txt' );
	echo '</form>';
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
		if (
			isset( $_POST['cbp_llms_export_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cbp_llms_export_nonce'] ) ), 'cbp_llms_export_action' )
		) {
			$raw_selected = isset( $_POST['cbp_llms_post_types'] ) ? $_POST['cbp_llms_post_types'] : array_keys( $post_types );
			$selected     = array();
			if ( is_array( $raw_selected ) ) {
				$selected = array_map( 'sanitize_text_field', wp_unslash( $raw_selected ) );
			}
			// Save selections.
			update_option( 'cbp_llms_selected_post_types', $selected );
			// Save summary.
			$summary = isset( $_POST['cbp_llms_summary'] ) ? sanitize_text_field( wp_unslash( $_POST['cbp_llms_summary'] ) ) : '';
			update_option( 'cbp_llms_summary', $summary );
			// Reload summary from DB so textarea always shows latest value.
			$summary = get_option( 'cbp_llms_summary', '' );
			$output  = cbp_llms_generate_output( $selected, $summary );
			$file    = ABSPATH . 'llms.txt';
			global $wp_filesystem;
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
			if ( $wp_filesystem->put_contents( $file, $output, FS_CHMOD_FILE ) ) {
				echo '<h2>Export Complete</h2>';
				echo '<p>Output written to <code>llms.txt</code> in site root.</p>';
				echo '<pre>' . esc_html( $output ) . '</pre>';
			} else {
				echo '<h2>Export Failed</h2>';
				echo '<p>Could not write to <code>llms.txt</code>. Check file permissions.</p>';
			}
		} else {
			echo '<h2>Export Failed</h2>';
			echo '<p>Security check failed. Please try again.</p>';
		}
	}
	echo '</div>';
}

/**
 * Generates output in markdown syntax for selected post types.
 *
 * @param array  $post_types Array of post type slugs.
 * @param string $summary One-line summary for the site.
 * @return string Markdown formatted list of posts.
 */
function cbp_llms_generate_output( $post_types, $summary = '' ) {
	$site_title = get_bloginfo( 'name' );
	$lines      = array( '# ' . $site_title, '' );
	if ( ! empty( $summary ) ) {
		$lines[] = '> ' . $summary;
		$lines[] = '';
	}
	foreach ( $post_types as $pt ) {
		$pt_obj        = get_post_type_object( $pt );
		$section_name  = $pt_obj ? $pt_obj->labels->name : ucfirst( $pt );
		$args          = array(
			'post_type'      => $pt,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		$query         = new WP_Query( $args );
		$section_lines = array();
		foreach ( $query->posts as $post ) {
			$noindex = get_post_meta( $post->ID, '_yoast_wpseo_meta-robots-noindex', true );
			if ( '1' === $noindex || 'yes' === $noindex ) {
				continue;
			}
			$title           = get_the_title( $post );
			$url             = get_permalink( $post );
			$section_lines[] = '- [' . $title . '](' . $url . ')';
		}
		wp_reset_postdata();
		if ( ! empty( $section_lines ) ) {
			$lines[] = '## ' . $section_name;
			$lines   = array_merge( $lines, $section_lines );
			$lines[] = '';
		}
	}
	return implode( "\n", $lines );
}
