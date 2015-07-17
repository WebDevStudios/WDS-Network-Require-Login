<?php

require_once 'admin-base.php';

/**
 * WDS Network Require Login Network Admin
 * @version 0.1.0
 * @package WDS Network Require Login
 */
class WDSNRL_Network_Admin extends WDSNRL_Admin_Base {

	/**
	 * The hook where the option menu should be attached
	 * @var string
	 */
	protected $admin_menu_hook = 'network_admin_menu';

	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	protected $key = 'wds_network_level_require_login';

	/**
 	 * Settings page metabox id
 	 * @var string
 	 */
	protected $metabox_id = 'wds_network_level_require_login';

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		parent::__construct( __( 'Require Login Network Settings', 'wds-network-require-login' ) );
	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		parent::hooks();

		// Override CMB's getter
		add_filter( 'cmb2_override_option_get_'. $this->key, array( $this, '_get_override' ), 10, 2 );
		// Override CMB's setter
		add_filter( 'cmb2_override_option_save_'. $this->key, array( $this, '_update_override' ), 10, 2 );
	}

	/**
	 * Returns result of add_submenu_page
	 *
	 * @since  0.1.0
	 * @return string Admin page hook
	 */
	protected function add_menu_page() {
		return add_submenu_page( 'settings.php', $this->title, $this->menu_title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
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
				'before' => '<style type="text/css" media="screen">.cmb2-id-enable-network-wide .cmb-td {padding: 24px 0;}</style>',
				'name'   => __( 'Require login network-wide', 'wds-network-require-login' ),
				'desc'   => __( 'This can be overridden at the site level.', 'wds-network-require-login' ),
				'id'     => 'enable_network_wide',
				'type'   => 'checkbox',
			),
		);
	}

	/**
	 * Replaces get_option with get_site_option
	 * @since  0.1.0
	 */
	public function _get_override( $test, $default = false ) {
		return get_site_option( $this->key, $default );
	}

	/**
	 * Replaces update_option with update_site_option
	 * @since  0.1.0
	 */
	public function _update_override( $test, $option_value ) {
		return update_site_option( $this->key, $option_value );
	}

}
