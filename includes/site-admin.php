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
		$this->network_admin = is_a( $network_admin, 'WDSNRL_Network_Admin' ) ? $network_admin : false;
	}

	/**
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

		return array(
			array(
				'name'    => __( 'Bypass login requirement for the REST API', 'wds-allow-rest-api' ),
				'desc'    => $desc,
				'id'      => 'allow_rest_api',
				'type'    => 'radio',
				'default' => array( $this, 'get_default' ),
				'options' => $options,
			),
		);
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
		$setting = wds_nrl()->admin->get_option( 'allow_rest_api' );

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