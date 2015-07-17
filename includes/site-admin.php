<?php

require_once 'admin-base.php';

/**
 * WDS Site Require Login Site Admin
 * @version 0.1.0
 * @package WDS Site Require Login
 */
class WDSNRL_Site_Admin extends WDSNRL_Admin_Base {

	/**
	 * Network admin object
	 * @var WDSNRL_Network_Admin
	 */
	protected $network_admin = null;

	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	protected $key = 'wds_network_require_login';

	/**
 	 * Settings page metabox id
 	 * @var string
 	 */
	protected $metabox_id = 'wds_network_require_login';

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct( WDSNRL_Network_Admin $network_admin ) {
		$this->network_admin = $network_admin;
		parent::__construct( __( 'Require Login Settings', 'wds-network-require-login' ) );
	}

	/**
	 * Returns result of add_submenu_page
	 *
	 * @since  0.1.0
	 * @return string Admin page hook
	 */
	protected function add_menu_page() {
		return add_options_page( $this->title, $this->menu_title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
	}

	/**
	 * Need to extend and should array of CMB2 field config arrays
	 *
	 * @since  0.1.0
	 * @return array Array of CMB2 field config arrays
	 */
	protected function fields() {
		return array(
			array(
				'before' => '<style type="text/css" media="screen">.cmb2-id-require .cmb-td {padding: 24px 0;}</style>',
				'name'    => __( 'Require login', 'wds-network-require-login' ),
				'desc'    => __( 'Enable or disable login requirement. Will override network level setting.', 'wds-network-require-login' ),
				'id'      => 'require',
				'type'    => 'radio',
				'default' => 'network_setting',
				'options' => array(
					'enabled'  => __( 'Enabled', 'wds-network-require-login' ),
					'disabled' => __( 'Disabled', 'wds-network-require-login' ),
					'network_setting' => sprintf( __( 'Use network level setting (set to <strong>%s</strong>)', 'wds-network-require-login' ), $this->network_setting() ),
				),
			),
		);
	}

	/**
	 * Gets network setting label, enabled or disabled
	 *
	 * @since  0.1.0
	 *
	 * @return string  Network setting label
	 */
	public function network_setting() {
		return $this->network_admin->get_option( 'enable_network_wide' )
			? __( 'enabled', 'wds-network-require-login' )
			: __( 'disabled', 'wds-network-require-login' );
	}

	/**
	 * Checks if login is required on this site.
	 * wds_network_require_login_is_required filter to override.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean Enabled or disabled
	 */
	public function is_required() {
		$is_required = $this->get_required_option_bool();
		return (bool) apply_filters( 'wds_network_require_login_is_required', $is_required );
	}

	/**
	 * Checks if login is required on this site. is_required for public access
	 *
	 * @since  0.1.0
	 *
	 * @return boolean Enabled or disabled
	 */
	protected function get_required_option_bool() {
		$setting = $this->get_option( 'require' );

		if ( 'enabled' === $setting ) {
			return true;
		}

		if ( 'disabled' === $setting ) {
			return false;
		}

		return (bool) $this->network_admin->get_option( 'enable_network_wide' );
	}

}
