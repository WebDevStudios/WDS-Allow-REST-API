<?php
/**
 * Plugin Name: WDS Allow REST API
 * Plugin URI:  http://webdevstudios.com
 * Description: If using the "WDS Network Require Login" plugin, this plugin adds some options for bypassing the login requirement for the REST API.
 * Version:     0.1.1
 * Author:      WebDevStudios
 * Author URI:  http://webdevstudios.com
 * Donate link: http://webdevstudios.com
 * License:     GPLv2
 * Text Domain: wds-allow-rest-api
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
 * @var  WDSARA_Admin         $admin         WDSARA_Admin
 * @var  WDSARA_Network_Admin $network_admin WDSARA_Network_Admin
 */
class WDS_Allow_REST_API {

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
	 * Instance of WDSARA_Network_Admin
	 *
	 * @var WDSARA_Network_Admin
	 * @since  0.1.0
	 */
	protected $network_admin = null;

	/**
	 * Instance of WDSARA_Admin
	 *
	 * @var WDSARA_Admin
	 * @since  0.1.0
	 */
	protected $admin = null;

	/**
	 * Singleton instance of plugin
	 *
	 * @var WDS_Allow_REST_API
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @return WDS_Allow_REST_API A single instance of this class.
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
		if ( is_multisite() ) {
			require_once $this->path . 'includes/network-admin.php';
			// Attach other plugin classes to the base plugin class.
			$this->network_admin = new WDSARA_Network_Admin();
		}

		require_once $this->path . 'includes/site-admin.php';
		// Attach other plugin classes to the base plugin class.
		$this->admin = new WDSARA_Site_Admin( $this->network_admin );
	}

	/**
	 * Add hooks and filters
	 *
	 * @since 0.1.0
	 * @return null
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'wds_network_require_login_for_rest_api', array( $this, 'maybe_allow_rest_api' ) );

		if ( is_multisite() ) {
			$this->network_admin->hooks();
		}

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
			load_plugin_textdomain( 'wds-allow-rest-api', false, dirname( $this->basename ) . '/languages/' );
		}
	}

	/**
	 * Check if auth redirect should happen for the wp rest api
	 *
	 * @since  0.1.0
	 * @return null
	 */
	public function maybe_allow_rest_api( $is_required ) {
		if ( is_user_logged_in() ) {
			return false;
		}

		// Check if authorization header token is enabled and if so, if it's found
		if ( $this->admin->get_option( 'auth_key' ) && $this->has_authorization_header() ) {
			return false;
		}

		return $this->admin->is_required_for_rest( $is_required );
	}

	/**
	 * Get the authorization header
	 *
	 * @since  0.1.1
	 * @return string|null Authorization header if set, null otherwise
	 */
	public function has_authorization_header() {

		$auth_key   = strtolower( $this->admin->get_option( 'auth_key' ) );
		$auth_value = $this->admin->get_option( 'auth_value' );

		// Check for the authoization header case-insensitively
		foreach ( $this->get_all_headers() as $key => $value ) {
			if ( $auth_key == strtolower( $key ) ) {
				return $value == $auth_value;
			}
		}

		return false;
	}

	/**
	 * Get the requrest headers. getallheaders wrapper
	 *
	 * @since  0.1.1
	 * @return array Array of request headers
	 */
	public function get_all_headers() {
		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
		} else {
			$headers = '';
			foreach ( $_SERVER as $name => $value )  {
				if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
					$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
				}
			}
		}

		return $headers;
	}

	/**
	 * Check that the wds-network-require-login plugin requirement is met
	 *
	 * @link   https://github.com/WebDevStudios/WDS-Network-Require-Login WDS Network Require Login plugin
	 * @since  0.1.0
	 * @return null
	 */
	function has_wds_network_require_login() {
		// If WDS Network Require Login is already loaded, great!
		if ( class_exists( 'WDS_Network_Require_Login' ) ) {
			return true;
		}

		$plugin = 'wds-network-require-login/wds-network-require-login.php';

		$plugins_activated         = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$plugins_network_activated = apply_filters( 'active_sitewide_plugins', get_site_option( 'active_sitewide_plugins' ) );

		// Normal plugin activation looks different than network-wide activation
		$plugin_activated         = in_array( $plugin, $plugins_activated );
		$plugin_network_activated = is_array( $plugins_network_activated ) && array_key_exists( $plugin, $plugins_network_activated );

		// If WDS Network Require Login plugin is active, we're ok to procceed
		return $plugin_activated || $plugin_network_activated;
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.1.0
	 * @return boolean result of has_wds_network_require_login
	 */
	public function check_requirements() {
		if ( ! $this->has_wds_network_require_login() ) {

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
	 * @link   https://github.com/WebDevStudios/WDS-Network-Require-Login WDS Network Require Login plugin
	 * @since  0.1.0
	 * @return null
	 */
	public function requirements_not_met_notice() {
		// Output our error
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'WDS Allow REST API the <a href="https://github.com/WebDevStudios/WDS-Network-Require-Login">WDS Network Require Login plugin</a>, so it has been <a href="%s">deactivated</a>.', 'wds-allow-rest-api' ), admin_url( 'plugins.php' ) ) . '</p>';
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
			case 'network_admin':
			case 'admin':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}
}

/**
 * Grab the WDS_Allow_REST_API object and return it.
 * Wrapper for WDS_Allow_REST_API::get_instance()
 *
 * @since  0.1.0
 * @return WDS_Allow_REST_API  Singleton instance of plugin class.
 */
function wds_allow_rest_api() {
	return WDS_Allow_REST_API::get_instance();
}

// Kick it off
wds_allow_rest_api();
