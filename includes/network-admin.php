<?php

require_once 'admin-base.php';

/**
 * WDS Allow REST API Network Admin
 * @version 0.1.0
 * @package WDS Allow REST API
 */
class WDSARA_Network_Admin extends WDSARA_Admin_Base {

	/**
 	 * Settings page metabox id
 	 * @var string
 	 */
	protected $metabox_id = 'wds_network_level_require_login';

	/**
	 * Additional fields for the $metabox_id CMB box instance
	 *
	 * @since 0.1.0
	 */
	function fields() {
		return array(
			array(
				'name'   => __( 'Bypass network-wide login requirement for the REST API', 'wds-network-require-login' ),
				'desc'   => __( 'This can be overridden at the site level.', 'wds-network-require-login' ),
				'id'     => 'allow_rest_api_network_wide',
				'type'    => 'radio',
				'default' => 'use_require_login_setting',
				'options' => array(
					'bypass'                    => __( '<strong>Bypass</strong> login requirement', 'wds-allow-rest-api' ),
					'require'                   => __( '<strong>Always require</strong> login for REST API on this network', 'wds-allow-rest-api' ),
					'use_require_login_setting' => __( 'Use <strong>Require login network-wide</strong> setting', 'wds-allow-rest-api' ),
				),
			),
		);
	}

	/**
	 * Checks if REST API bypass is allowed on this network.
	 *
	 * @since  0.1.0
	 * @return boolean Enabled or disabled
	 */
	public function is_required_for_rest() {
		$setting = wds_nrl()->network_admin->get_option( 'allow_rest_api_network_wide' );

		if ( 'bypass' === $setting ) {
			return false;
		}

		if ( 'require' === $setting ) {
			return true;
		}

		return (bool) wds_nrl()->network_admin->get_option( 'enable_network_wide' );
	}

}
