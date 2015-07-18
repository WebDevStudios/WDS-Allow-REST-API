<?php

require_once 'admin-base.php';

/**
 * WDS Allow REST API Network Admin
 * @version 0.1.0
 * @package WDS Allow REST API
 */
class WDSARA_Site_Admin extends WDSARA_Admin_Base {

	/**
 	 * Settings page metabox id
 	 * @var string
 	 */
	protected $metabox_id = 'wds_network_require_login';

	/**
	 * Network admin object
	 * @var WDSARA_Network_Admin|false
	 */
	protected $network_admin = false;

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct( $network_admin ) {
		$this->network_admin = is_a( $network_admin, 'WDSARA_Network_Admin' ) ? $network_admin : false;
	}

	/**
	 * Additional fields for the $metabox_id CMB box instance
	 *
	 * @since 0.1.0
	 */
	function fields() {
		$options = array(
			'bypass'                    => __( '<strong>Bypass</strong> login requirement', 'wds-allow-rest-api' ),
			'require'                   => __( '<strong>Always require</strong> login for REST API on this site', 'wds-allow-rest-api' ),
			'use_require_login_setting' => __( 'Use <strong>Require login</strong> setting', 'wds-allow-rest-api' ),
		);

		$desc = __( 'Enable or disable login requirement for the REST API.', 'wds-allow-rest-api' );

		if ( $this->network_admin ) {
			$options['use_network_setting'] = sprintf( __( '<strong>Use network level settin</strong>g (set to <strong>%s</strong>)', 'wds-allow-rest-api' ), $this->network_setting() );
			$desc .= ' '. __( 'Will override network level setting.', 'wds-allow-rest-api' );
		}

		$fields = array(
			array(
				'name'    => __( 'Bypass login requirement for the REST API', 'wds-allow-rest-api' ),
				'desc'    => $desc,
				'id'      => 'allow_rest_api',
				'type'    => 'radio',
				'default' => array( $this, 'get_default' ),
				'options' => $options,
			),
			array(
				'name'    => __( 'Optional REST API authorization override token', 'wds-allow-rest-api' ),
				'desc'    => __( 'If login is required for REST API, this is an optional override which clients would pass as an additional header key and value to bypass the login requirement.<br>You will need to provide this key and token value when making requests to the REST API.', 'wds-allow-rest-api' ),
				'id'      => 'authorization_override_title',
				'type'    => 'title',
				'show_on_cb' => array( $this, 'any_required' ),
			),
			array(
				'name'    => __( 'Authorization override key', 'wds-allow-rest-api' ),
				'desc'    => __( 'This is the header key. Recommended something like <code>wp-rest-authorizationkey</code>', 'wds-allow-rest-api' ),
				'id'      => 'auth_key',
				'type'    => 'text',
				'attributes' => array( 'placeholder' => 'wp-rest-authorizationkey' ),
				'show_on_cb' => array( $this, 'any_required' ),
			),
			array(
				'name'    => __( 'Authorization override token', 'wds-allow-rest-api' ),
				'desc'    => __( 'This is the header value. This should be a lengthy and unique value. Will be generated for you.', 'wds-allow-rest-api' ),
				'id'      => 'auth_value',
				'type'    => 'text',
				'default' => array( $this, 'get_auth_token_value_default' ),
				'show_on_cb' => array( $this, 'any_required' ),
			),
		);

		return $fields;
	}

	/**
	 * Generates a default long, unique value for the authentication token value
	 *
	 * @since  0.1.1
	 *
	 * @return string  Generated token value
	 */
	public function get_auth_token_value_default() {
		return wp_generate_password( 32, false );
	}

	/**
	 * Field show_on callback which allows field to show if
	 * it's determined that auth for REST API is required
	 *
	 * @since  0.1.1
	 *
	 * @return bool Whether field should show
	 */
	public function any_required() {
		return $this->is_required_for_rest( wds_nrl()->admin->is_required() );
	}

	/**
	 * Get default value for our setting
	 *
	 * @since  0.1.0
	 *
	 * @return string Default value
	 */
	public function get_default() {
		return $this->network_admin ? 'use_network_setting' : 'use_require_login_setting';
	}

	/**
	 * Gets network setting label, enabled or disabled
	 *
	 * @since  0.1.0
	 *
	 * @return string  Network setting label
	 */
	public function network_setting() {
		return $this->network_admin->is_required_for_rest()
			? __( 'required', 'wds-allow-rest-api' )
			: __( 'bypassed', 'wds-allow-rest-api' );
	}

	/**
	 * Checks if REST API bypass is allowed on this site.
	 *
	 * @since  0.1.0
	 * @return boolean Enabled or disabled
	 */
	public function is_required_for_rest( $is_required ) {
		$setting = $this->get_option( 'allow_rest_api' );

		if ( 'bypass' === $setting ) {
			return false;
		}

		if ( 'require' === $setting ) {
			return true;
		}

		if ( 'use_require_login_setting' === $setting ) {
			return $is_required;
		}

		if ( $this->network_admin ) {
			return (bool) $this->network_admin->is_required_for_rest();
		}

		return $is_required;
	}

}
