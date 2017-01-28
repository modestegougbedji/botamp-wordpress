<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'traits/option.php';

class Botamp_Woocommerce_Admin {

	use Option;

	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function register_settings() {
		register_setting( $this->plugin_name, $this->option( 'order_notifications' ) );
	}

	public function order_notifications_cb() {
		$current_state = $this->get_option( 'order_notifications' );
		$html = '<input type="checkbox" name="' . $this->option( 'order_notifications' ) . '" value="enabled" ' .
		checked( 'enabled', $current_state, false ) . '/>';
			$html .= '<label for="' . $this->option( 'order_notifications' ) . '"> Send order notifications to users </label>';
		echo $html;

	}
}
