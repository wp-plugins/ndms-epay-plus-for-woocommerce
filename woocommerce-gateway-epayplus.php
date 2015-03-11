<?php
/*
Plugin Name: WooCommerce ePay+ Gateway
Plugin URI: http://ndmscorp.com/epay-plus/
Description: Extends WooCommerce with a <a href="http://www.ndmscorp.com/epay-plus/" target="_blank">ePay+</a> gateway. A ePay+ gateway account, and a server with SSL support and an SSL certificate is required for security reasons.
Author: NDMSCorp
Author URI: http://ndmscorp.com/epay-plus/
Version: 1.0.3
Text Domain: wc-gateway-epayplus
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


/**
 * Growdev Order Fulfillment Main Class
 *
 * @package  Growdev Order Fulfillment
 */

class WC_Epayplus {

	protected static $instance = null;

	/**
	 *  Constructor
	 */
	function __construct() {

		if ( class_exists( 'WooCommerce' ) ) {

			include_once( 'includes/class-wc-gateway-epayplus.php' );

			if ( class_exists( 'WC_Subscriptions_Order' ) ) {
				include_once( 'includes/class-wc-gateway-epayplus-subscriptions.php' );
			}

			load_plugin_textdomain( 'wc-gateway-epayplus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			define( 'EPAYPLUS_DIR', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) . '/' );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_links' ) );
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_epayplus_gateway') );

		} else {

			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );

		}

	}

	/**
	 * Plugin page links
	 *
	 * @param array $links
	 * @return array
	 */
	function plugin_links( $links ) {

		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_epayplus' ) . '">' .
			__( 'Settings', 'wc-gateway-epayplus' ) . '</a>',
			'<a href="http://www.ndmscorp.com/epay-plus/">' . __( 'Docs', 'wc-gateway-epayplus' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Add the gateway to woocommerce
	 *
	 * @param array $methods
	 * @return array
	 **/
	function add_epayplus_gateway( $methods ) {

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {
			$methods[] = 'WC_Gateway_Epayplus_Subscriptions';
		} else {
			$methods[] = 'WC_Gateway_Epayplus';
		}

		return $methods;
	}

	/**
	 * Start the Class when called
	 *
	 * @return WC_Epayplus
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}


	/**
	 * WooCommerce fallback notice.
	 *
	 * @return string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce ePay+ requires %s to be installed and active.', 'wc-gateway-epayplus' ), '<a href="http://woocommerce.com/" target="_blank">' . __( 'WooCommerce', 'wc-gateway-epayplus' ) . '</a>' ) . '</p></div>';
	}



}

add_action( 'plugins_loaded', array( 'WC_Epayplus', 'get_instance' ) );