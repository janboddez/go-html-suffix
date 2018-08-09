<?php
/**
 * Plugin Name: HTML Suffix in URLs
 * Plugin URI: https://github.com/janboddez/go-html-suffix
 * GitHub PLugin URI: https://github.com/janboddez/go-html-suffix
 * Description: Appends '.html' to page URLs.
 * Author: Jan Boddez
 * Author URI: https://www.janboddez.be/
 * Version: 0.1
 */

/* Prevents this script from being accessed directly. */
defined( 'ABSPATH' ) or exit;

class GO_Html_Suffix {
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		add_action( 'init', array( $this, 'set_page_permalink' ), 0 );
		add_filter( 'user_trailingslashit', array( $this, 'modified_trailingslashit' ), 99, 2 );
	}

	/**
	 * On activation, updates rewrite rules with the new URL structure.
	 */
	public function activate() {
		global $wp_rewrite;
		// Store old page permastruct.
		update_option( 'go_html_suffix_old_page_permastruct', $wp_rewrite->get_page_permastruct() );
		$this->set_page_permalink();
		$wp_rewrite->flush_rules();
	}

	/**
	 * On deactivation, restores rewrite rules.
	 */
	public function deactivate() {
		global $wp_rewrite;
		$old_page_permastruct = get_option( 'go_html_suffix_old_page_permastruct', false );

		if ( false !== $old_page_permastruct ) {
			// Restore original URL structure.
			$wp_rewrite->page_structure = $old_page_permastruct;
		} elseif ( '.html' === substr( $page_permastruct, -5 ) ) {
			// Remove '.html' from end of URLs.
			$wp_rewrite->page_structure = substr( $page_permastruct, 0, -5 );
		}

		$wp_rewrite->flush_rules();
	}

	/**
	 * Ensures page permalinks end in '.html'.
	 */
	public function set_page_permalink() {
		global $wp_rewrite;
		$page_permastruct = $wp_rewrite->get_page_permastruct();

		if ( '.html' !== substr( $page_permastruct, -5 ) ) {
			$wp_rewrite->page_structure = $page_permastruct . '.html';
		}
	}

	/** 
	 * Filter `user_trailingslashit()` so that trailing slashes are always removed
	 * from page URLs. (They should end in '.html', remember?)
	 *
	 * @param string $string (Part of) the URL to be filtered.
	 * @param string $type_of_url URL type. Accepts `'page'` and other values.
	 *
	 * @return string Filtered URL (i.e., without trailing slash if referring to a page and unaltered otherwise).
	 */
	public function modified_trailingslashit( $string, $type_of_url ) {
		if ( 'page' === $type_of_url ) {
			return untrailingslashit( $string );
		}

		return $string;
	}
}

new GO_Html_Suffix();
