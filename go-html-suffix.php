<?php
/**
 * Plugin Name: HTML Suffix in URLs
 * Plugin URI: https://github.com/janboddez/go-html-suffix
 * GitHub PLugin URI: https://github.com/janboddez/go-html-suffix
 * Description: Appends '.html' to page URLs.
 * Author: Jan Boddez, WIT Solution
 * Version: 0.1
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @author Jan Boddez [jan@janboddez.be]
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 * @package GO_Html_Suffix
 */

/* Prevents this script from being accessed directly. */
defined( 'ABSPATH' ) or exit;

class GO_Html_Suffix {
	/**
	 * Registers hooks.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		add_action( 'init', array( $this, 'set_page_permalink' ), 0 );
		add_filter( 'user_trailingslashit', array( $this, 'modified_trailingslashit' ), 99, 2 );
	}

	/**
	 * On activation, updates rewrite rules with the new URL structure.
	 *
	 * @since 0.1
	 */
	public function activate() {
		global $wp_rewrite;
		$this->set_page_permalink();
		$wp_rewrite->flush_rules();
	}

	/**
	 * On deactivation, restores rewrite rules.
	 *
	 * @since 0.1
	 */
	public function deactivate() {
		global $wp_rewrite;
		$page_permastruct = $wp_rewrite->get_page_permastruct();

		if ( false !== $page_permastruct && '.html' === substr( $page_permastruct, -5 ) ) {
			// Remove '.html' from end of URLs.
			$wp_rewrite->page_structure = substr( $page_permastruct, 0, -5 );
		}

		$wp_rewrite->flush_rules();
	}

	/**
	 * Ensures page permalinks end in '.html'.
	 *
	 * @since 0.1
	 */
	public function set_page_permalink() {
		global $wp_rewrite;
		$page_permastruct = $wp_rewrite->get_page_permastruct();

		if ( false !== $page_permastruct && '.html' !== substr( $page_permastruct, -5 ) ) {
			$wp_rewrite->page_structure = $page_permastruct . '.html';
		}
	}

	/**
	 * Filter `user_trailingslashit()` so that trailing slashes are always
	 * removed from page URLs. (They should end in '.html', remember?)
	 *
	 * @since 0.1
	 *
	 * @param string $string (Part of) the URL to be filtered.
	 * @param string $type_of_url URL type. Accepts 'page' and other values.
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
