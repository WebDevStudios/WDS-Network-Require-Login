<?php
/**
 * Plugin Name: WDS Network Require Login
 * Plugin URI:  http://webdevstudios.com
 * Description: A require-login plugin that can be network-activated as well as overridden on the site level.
 * Version:     0.1.0
 * Author:      WebDevStudios
 * Author URI:  http://webdevstudios.com
 * Donate link: http://webdevstudios.com
 * License:     GPLv2
 * Text Domain: wds-network-require-login
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2015 WebDevStudios (email : contact@webdevstudios.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */

/**
 * Main initiation class
 *
 * @since  0.1.0
 * @var  string               $version       Plugin version
 * @var  string               $basename      Plugin basename
 * @var  string               $url           Plugin URL
 * @var  string               $path          Plugin Path
 * @var  string               $current_url   Current URL
 * @var  WDSNRL_Admin         $admin         WDSNRL_Admin
 * @var  WDSNRL_Network_Admin $network_admin WDSNRL_Network_Admin
 */
class WDS_Network_Require_Login {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  0.1.0
	 */
	const VERSION = '0.1.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Current URL
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $current_url = '';

	/**
	 * Instance of WDSNRL_Network_Admin
	 *
	 * @var WDSNRL_Network_Admin
	 * @since  0.1.0
	 */
	protected $network_admin = null;

	/**
	 * Instance of WDSNRL_Admin
	 *
	 * @var WDSNRL_Admin
	 * @since  0.1.0
	 */
	protected $admin = null;

	/**
	 * Singleton instance of plugin
	 *
	 * @var WDS_Network_Require_Login
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @return WDS_Network_Require_Login A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename    = plugin_basename( __FILE__ );
		$this->url         = plugin_dir_url( __FILE__ );
		$this->path        = plugin_dir_path( __FILE__ );
		$this->current_url = self::get_url();

		$this->plugin_classes();
		$this->hooks();
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since 0.1.0
	 * @return  null
	 */
	function plugin_classes() {
		require_once $this->path . 'includes/network-admin.php';
		// Attach other plugin classes to the base plugin class.
		$this->network_admin = new WDSNRL_Network_Admin();

		require_once $this->path . 'includes/site-admin.php';
		// Attach other plugin classes to the base plugin class.
		$this->admin = new WDSNRL_Site_Admin( $this->network_admin );
	}

	/**
	 * Add hooks and filters
	 *
	 * @since 0.1.0
	 * @return null
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'get_header', array( $this, 'maybe_auth_redirect' ) );
		add_action( 'rest_api_init', array( $this, 'rest_maybe_auth_redirect' ) );
		$this->network_admin->hooks();
		$this->admin->hooks();
	}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 * @return null
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'wds-network-require-login', false, dirname( $this->basename ) . '/languages/' );
		}
	}

	/**
	 * Check if auth redirect should happen for the wp rest api
	 *
	 * @since  0.1.0
	 * @return null
	 */
	public function rest_maybe_auth_redirect() {
		if ( is_user_logged_in() ) {
			return;
		}

		// Option to override saved setting for just the wp rest api
		if ( apply_filters( 'wds_network_require_login_for_rest_api', $this->admin->is_required() ) ) {
			$this->auth_redirect();
		}

	}

	/**
	 * Check if auth redirect should happen on normal page-load
	 *
	 * @since  0.1.0
	 * @return null
	 */
	public function maybe_auth_redirect() {
		if ( is_user_logged_in() || ! $this->admin->is_required() ) {
			return;
		}

		$this->auth_redirect();
	}

	/**
	 * Wrapper for auth_redirect which filters the login_url before calling
	 * And checks if URL is whitelisted
	 *
	 * @since  0.1.0
	 * @return null
	 */
	public function auth_redirect() {
		$whitelist   = apply_filters( 'wds_network_require_login_whitelist', array() );
		$whitelisted = in_array( $this->current_url, $whitelist );

		$curr_url    = preg_replace( '/\?.*/', '', $this->current_url );
		$login_url   = preg_replace( '/\?.*/', '', wp_login_url() );

		if ( $login_url == $curr_url || $whitelisted ) {
			return;
		}

		// Ok, do our redirect
		add_filter( 'login_url', array( $this, 'add_our_login_url_filter' ), 10, 3 );
		auth_redirect();
		remove_filter( 'login_url', array( $this, 'add_our_login_url_filter' ), 10, 3 );
	}

	/**
	 * Add a filter on top of the 'login_url' filter to only apply during rquire-login redirects
	 *
	 * @since  0.1.0
	 * @return mixed
	 */
	public function add_our_login_url_filter( $login_url, $redirect, $force_reauth ) {
		return apply_filters( 'wds_network_require_login_redirect_url', $login_url, $redirect, $force_reauth, $this->current_url );
	}

	/**
	 * Gets the current URL
	 *
	 * @since  0.1.0
	 *
	 * @return string  Current URL (or site_url if server info is not found)
	 */
	public static function get_url() {
		if ( ! isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ) {
			return site_url();
		}

		$has_port = isset( $_SERVER['SERVER_PORT'] ) && ! in_array( $_SERVER['SERVER_PORT'], array('80', '443') );

		$url  = is_ssl() ? 'https' : 'http';
		$url .= '://' . $_SERVER['HTTP_HOST'];
		$url .= $has_port ? ':' . $_SERVER['SERVER_PORT'] : '';
		$url .= $_SERVER['REQUEST_URI'];

		return $url;
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  0.1.0
	 * @return boolean
	 */
	public static function meets_requirements() {
		// Plugin requires multisite & CMB2
		return is_multisite() && defined( 'CMB2_LOADED' );
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.1.0
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {

			// Add a dashboard notice
			add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

			// Deactivate our plugin
			deactivate_plugins( $this->basename );

			return false;
		}

		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met
	 *
	 * @since  0.1.0
	 * @return null
	 */
	public function requirements_not_met_notice() {
		// Output our error
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'WDS Network Require Login is meant for a WordPress multisite installations, and requires the <a href="https://wordpress.org/plugins/cmb2/">CMB2 plugin</a>, so it has been <a href="%s">deactivated</a>.', 'wds-network-require-login' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'current_url':
			case 'network_admin':
			case 'admin':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}
}

/**
 * Grab the WDS_Network_Require_Login object and return it.
 * Wrapper for WDS_Network_Require_Login::get_instance()
 *
 * @since  0.1.0
 * @return WDS_Network_Require_Login  Singleton instance of plugin class.
 */
function wds_nrl() {
	return WDS_Network_Require_Login::get_instance();
}

// Kick it off
wds_nrl();
