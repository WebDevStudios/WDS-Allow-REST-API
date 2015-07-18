<?php

/**
 * WDS Allow REST API Network Admin
 * @version 0.1.0
 * @package WDS Allow REST API
 */
abstract class WDSARA_Admin_Base {

	/**
 	 * Settings page metabox id
 	 * @var string
 	 */
	protected $metabox_id = '';

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'cmb2_init_before_hookup', array( $this, 'add_rest_api_override_field' ) );
	}

	/**
	 * Add another field to the $metabox_id CMB box instance
	 *
	 * @since 0.1.0
	 */
	function add_rest_api_override_field() {

		// Retrieve the CMB2 instance
		$cmb = cmb2_get_metabox( $this->metabox_id );
		if ( ! $cmb ) {
			return;
		}

		foreach ( $this->fields() as $field ) {
			$cmb->add_field( $field );
		}
	}

	/**
	 * Get an option value
	 *
	 * @since  0.1.0
	 * @param  string $key Options array key
	 * @return mixed       Option value
	 */
	public function get_option( $key ) {
		return wds_nrl()->admin->get_option( $key );
	}

	/**
	 * Need to extend and should array of CMB2 field config arrays
	 *
	 * @since  0.1.0
	 * @return array Array of CMB2 field config arrays
	 */
	abstract protected function fields();
}
