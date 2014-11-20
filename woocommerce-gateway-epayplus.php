<?php
/*
Plugin Name: WooCommerce ePay+ Gateway
Plugin URI: http://ndmscorp.com/epay-plus/
Description: Extends WooCommerce with a <a href="http://www.ndmscorp.com/epay-plus/" target="_blank">ePay+</a> gateway. A ePay+ gateway account, and a server with SSL support and an SSL certificate is required for security reasons.
Author: NDMSCorp
Author URI: http://ndmscorp.com/epay-plus/
Version: 1.0.0
Text Domain: wc-gateway-epayplus
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) ) {  exit; } // Exit if accessed directly

add_action('plugins_loaded', 'woocommerce_gateway_epayplus_init', 0);

function woocommerce_gateway_epayplus_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'wc-gateway-epayplus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	define('EPAYPLUS_DIR', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) . '/');

	include_once 'epayplus.php';

	/**
	 * ePay+ Gateway Class
	 **/
	class WC_Gateway_Epayplus extends WC_Payment_Gateway {

		public function __construct() {

			$this->id   				= 'epayplus';
			$this->method_title 		= __( 'ePay+', 'wc-gateway-epayplus' );
			$this->method_description 	= __( 'ePay+ allows customers to checkout using a credit card', 'wc-gateway-epayplus');
			$this->logo 				= untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/images/logo.png';
			$this->icon   				= untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/images/cards.png';
			$this->has_fields  			= false;

			// Load the form fields
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Get setting values
			$this->enabled    		= $this->settings['enabled'];
			$this->title    		= $this->settings['title'];
			$this->description  	= $this->settings['description'];
			$this->trandescription  = $this->settings['trandescription'];
			$this->sourcekey  		= $this->settings['sourcekey'];
			$this->pin    			= $this->settings['pin'];
			$this->command   		= $this->settings['command'];
            $this->custreceipt      = $this->settings['custreceipt'];
			$this->testmode  		= $this->settings['testmode'];
			$this->usesandbox  		= $this->settings['usesandbox'];
			$this->cvv    			= $this->settings['cvv'];
			$this->debugon   		= $this->settings['debugon'];
			$this->debugrecipient  	= $this->settings['debugrecipient'];

			// Actions
			add_action('admin_notices', array(&$this, 'epayplus_ssl_check'));
			add_action('woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options'));
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options'));
	
		}


		/**
		 * Check if SSL is enabled and notify the user
		 */
		function epayplus_ssl_check() {

			if (get_option('woocommerce_force_ssl_checkout')=='no' && $this->enabled=='yes') :

				echo '<div class="error"><p>'.sprintf(__('ePay+ is enabled and the Force secure checkout option is disabled; your checkout is not secure! Please enable this feature and ensure your server has a valid SSL certificate installed.', 'wc-gateway-epayplus'), admin_url('admin.php?page=woocommerce')).'</p></div>';

			endif;
		}


		/**
		 * Initialize Gateway Settings Form Fields
		 */
		function init_form_fields() {

			$this->form_fields = array(
				'enabled' => array(
					'title' => __( 'Enable/Disable', 'wc-gateway-epayplus' ),
					'label' => __( 'Enable ePay+ Gateway', 'wc-gateway-epayplus' ),
					'type' => 'checkbox',
					'description' => '',
					'default' => 'no'
				),
				'title' => array(
					'title' => __( 'Title', 'wc-gateway-epayplus' ),
					'type' => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wc-gateway-epayplus' ),
					'default' => __( 'Credit card', 'wc-gateway-epayplus' )
				),
				'description' => array(
					'title' => __( 'Description', 'wc-gateway-epayplus' ),
					'type' => 'textarea',
					'description' => __( 'This controls the description which is displayed to the customer.', 'wc-gateway-epayplus' ),
					'default' => 'Pay with your MasterCard, Visa, Discover or American Express'
				),
				'trandescription' => array(
					'title' => __( 'Transaction Description', 'wc-gateway-epayplus' ),
					'type' => 'textarea',
					'description' => __( 'This controls the description that is added to the transaction sent to ePay+.', 'wc-gateway-epayplus' ),
					'default' => 'Order from WooCommerce.'
				),
				'sourcekey' => array(
					'title' => __( 'ePay+ Secure ID', 'wc-gateway-epayplus' ),
					'type' => 'text',
					'description' => __( 'Contact your NDMS Rep to get your Secure ID or Refer to the ePay+ Welcome email. If you dont have an ePay+ Account please visit our website <a href="http://epayplus.ndmscorp.com" target="_blank">http://epayplus.ndmscorp.com</a> or Call 310.997.0100 for more information.', 'wc-gateway-epayplus' ),
					'default' => ''
				),
				'pin' => array(
					'title' => __( 'ePay+ Secure Pin', 'wc-gateway-epayplus' ),
					'type' => 'text',
					'description' => __( 'Pin for ePay+ Secure ID . This field is required only if the merchant has set a Pin in the ePay+ console.', 'wc-gateway-epayplus' ),
					'default' => ''
				),
				'command' => array(
					'title' => __( 'Payment Type', 'wc-gateway-epayplus' ),
					'type' => 'select',
					'description' => __( 'Payment command to run. ', 'wc-gateway-epayplus' ),
					'options' => array('sale'=>'Sale',
						'authonly'=>'Authorize Only'),
					'default' => ''
				),
                'custreceipt' => array(
                    'title' => __( 'Receipt Email', 'wc-gateway-epayplus' ),
                    'label' => __( 'Send receipt email', 'wc-gateway-epayplus' ),
                    'type' => 'checkbox',
                    'description' => __( 'If checked ePay+ will send a receipt email to the customer in addition to WooCommerce order email.', 'wc-gateway-epayplus' ),
                    'default' => 'no'
                ),
				'testmode' => array(
					'title' => __( 'Test Mode', 'wc-gateway-epayplus' ),
					'label' => __( 'Enable Test Mode', 'wc-gateway-epayplus' ),
					'type' => 'checkbox',
					'description' => __( 'If checked then the transaction will be simulated by ePay+, but not actually processed.', 'wc-gateway-epayplus' ),
					'default' => 'no'
				),
				'usesandbox' => array(
					'title' => __( 'Sandbox', 'wc-gateway-epayplus' ),
					'label' => __( 'Enable Sandbox', 'wc-gateway-epayplus' ),
					'type' => 'checkbox',
					'description' => __( 'If checked the sandbox server will be used. Overrides the gateway url parameter.', 'wc-gateway-epayplus' ),
					'default' => 'no'
				),
				'cvv' => array(
					'title' => __( 'CVV', 'wc-gateway-epayplus' ),
					'label' => __( 'Require customer to enter credit card CVV code', 'wc-gateway-epayplus' ),
					'type' => 'checkbox',
					'description' => __( '', 'wc-gateway-epayplus' ),
					'default' => 'no'
				),
				'debugon' => array(
					'title' => __( 'Debugging', 'wc-gateway-epayplus' ),
					'label' => __( 'Enable debug emails', 'wc-gateway-epayplus' ),
					'type' => 'checkbox',
					'description' => __( 'Receive emails containing the data sent to and from ePay+.', 'wc-gateway-epayplus' ),
					'default' => 'no'
				),
				'debugrecipient' => array(
					'title' => __( 'Debugging Email', 'wc-gateway-epayplus' ),
					'type' => 'text',
					'description' => __( 'Who should receive the debugging emails.', 'wc-gateway-epayplus' ),
					'default' =>  get_option('admin_email')
				),
			);
		}


		/**
		 * Admin Panel Options
		 *
		 * @return void
		 */
		public function admin_options() {
?>
			<p><a href="http://www.ndmscorp.com/epay-plus/" target="_blank"><img src="<?php echo $this->logo;?>" /></a></p>
			<h3><?php _e('ePay+', 'wc-gateway-epayplus'); ?></h3>
	    	<p><?php _e( 'ePay+ allows customers to checkout using a credit card by adding credit card fields on the checkout page and then sending the details to ePay+ for verification.', 'wc-gateway-epayplus' ); ?></p>
	    	<table class="form-table">
	    		<?php $this->generate_settings_html(); ?>
			</table><!--/.form-table-->
	    	<?php
		}

		/**
		 * Payment fields for ePay+.
		 */
		function payment_fields() {
			?>
			<fieldset>
				<p class="form-row form-row-first">
					<label for="epayplus_ccnum"><?php echo __("Credit card number", 'wc-gateway-epayplus') ?> <span class="required">*</span></label>
					<input type="text" class="input-text" id="epayplus_ccnum" name="epayplus_ccnum" />
				</p>
				<div class="clear"></div>

				<p class="form-row">
					<label for="epayplus_cardholdername"><?php echo __("Card holder name", 'wc-gateway-epayplus') ?> <span class="required">*</span></label>
					<input type="text" class="input-text" id="epayplus_cardholdername" name="epayplus_cardholdername" />
				</p>
				<div class="clear"></div>

				<p class="form-row form-row-first">
					<label for="epayplus_expmonth"><?php echo __("Expiration month", 'wc-gateway-epayplus') ?> <span class="required">*</span></label>
					<select name="epayplus_expmonth" id="epayplus_expmonth" class="woocommerce-select woocommerce-cc-month">
						<option value=""><?php _e('Month', 'wc-gateway-epayplus') ?></option>
						<?php
							$months = array();
							for ($i = 1; $i <= 12; $i++) {
								$timestamp = mktime(0, 0, 0, $i, 1);
								$months[date('m', $timestamp)] = date('F', $timestamp);
							}
							foreach ($months as $num => $name) {
								printf('<option value="%s">%s</option>', $num, $name);
							}
						?>
					</select>
					<label for="epayplus_expyear"><?php echo __("Expiration year", 'wc-gateway-epayplus') ?> <span class="required">*</span></label>
					<select name="epayplus_expyear" id="epayplus_expyear" class="woocommerce-select woocommerce-cc-year">
						<option value=""><?php _e('Year', 'wc-gateway-epayplus') ?></option>
						<?php
							for ($i = date('y'); $i <= date('y') + 15; $i++) {
								printf('<option value="%u">20%u</option>', $i, $i);
							}
						?>
					</select>
				</p>
				
				<?php if ($this->cvv == 'yes') : ?>
					<p class="form-row form-row-last">
						<label for="epayplus_cvv"><?php _e("Card security code", 'wc-gateway-epayplus') ?> <span class="required">*</span></label>
						<input type="text" class="input-text" id="epayplus_cvv" name="epayplus_cvv" maxlength="4" style="width:45px" />
					</p>
				<?php endif; ?>
				
				<div class="clear"></div>
				<p><?php echo $this->description ?></p>
			</fieldset>
			<?php
		}


		/**
		 * Process the payment and return the result
		 * @param int $order_id
		 * @return mixed
		 */

		function process_payment( $order_id ) {

			$order = new WC_Order( $order_id );

			// Create request
			$tran = new umTransaction;

			$tran->key = $this->sourcekey;
			$tran->pin = $this->pin;
			$tran->ip = $_SERVER['REMOTE_ADDR']; // This allows fraud blocking on the customers ip address

            if ( $this->custreceipt == 'yes')
                $tran->custreceipt = 'yes';

			if ( $this->testmode == 'yes') {
				$tran->testmode = 'yes';
			} else {
				$tran->testmode = 0;
			}

			if ( $this->usesandbox == 'yes') {
				$tran->usesandbox = true;
			} else {
				$tran->usesandbox = false;
			}

			$tran->command = $this->command;

			$tran->card			= $this->get_post('epayplus_ccnum');
			$tran->exp			= $this->get_post('epayplus_expmonth') . $this->get_post('epayplus_expyear');
			$tran->amount		= $order->order_total;
			$tran->invoice		= $order->id;
			$tran->cardholder	= $this->get_post('epayplus_cardholdername');
			$tran->street		= $order->billing_address_1;
			$tran->zip			= $order->billing_postcode;
			$tran->description	= $this->trandescription;
			if ($this->cvv == 'yes') {
				$tran->cvv2   = $this->get_post('epayplus_cvv');
			}

			// Billing Fields
			$tran->billfname	= $order->billing_first_name;
			$tran->billlname	= $order->billing_last_name;
			$tran->billcompany	= $order->billing_company;
			$tran->billstreet	= $order->billing_address_1;
			$tran->billstreet2	= $order->billing_address_2;
			$tran->billcity		= $order->billing_city;
			$tran->billstate	= $order->billing_state;
			$tran->billzip		= $order->billing_postcode;
			$tran->billcountry	= $order->billing_country;
			$tran->billphone	= $order->billing_phone;
			$tran->email		= $order->billing_email;

			// Shipping Fields
			$tran->shipfname	= $order->shipping_first_name;
			$tran->shiplname	= $order->shipping_last_name;
			$tran->shipcompany	= $order->shipping_company;
			$tran->shipstreet	= $order->shipping_address_1;
			$tran->shipstreet2 	= $order->shipping_address_2;
			$tran->shipcity		= $order->shipping_city;
			$tran->shipstate	= $order->shipping_state;
			$tran->shipzip		= $order->shipping_postcode;
			$tran->shipcountry	= $order->shipping_country;

			$epayplus_request = array (
				"key" => $this->sourcekey,
				"pin" => $this->pin,
				"customer ip" => $_SERVER['REMOTE_ADDR'],
				"testmode" => $this->testmode,
				"usessandbox" => $this->usesandbox,
				"command" => $this->command,
				"card" => "[Card number not available for debug]",
				"expiration" => "[Expiration date not available for debug]",
				"amount" => $order->order_total,
				"order id" => $order->id,
				"cardholder" => $this->get_post('epayplus_cardholdername'),
				"street" => $order->billing_address_1,
				"zip" => $order->billing_postcode,
				"description" => $this->trandescription,
				"cvv" => $this->get_post('epayplus_cvv'),
				"billfname" => $order->billing_first_name,
				"billlname" => $order->billing_last_name,
				"billcompany" => $order->billing_company,
				"billstreet" => $order->billing_address_1,
				"billstreet2" => $order->billing_address_2,
				"billcity" => $order->billing_city,
				"billstate" => $order->billing_state,
				"billzip" => $order->billing_postcode,
				"billcountry" => $order->billing_country,
				"billphone" => $order->billing_phone,
				"email" => $order->billing_email,
				"shipfname" => $order->shipping_first_name,
				"shiplname" => $order->shipping_last_name,
				"shipcompany" => $order->shipping_company,
				"shipstreet" => $order->shipping_address_1,
				"shipstreet2" => $order->shipping_address_2,
				"shipcity" => $order->shipping_city,
				"shipstate" => $order->shipping_state,
				"shipzip" => $order->shipping_postcode,
				"shipcountry" => $order->shipping_country
			);

			$this->send_debugging_email( "SENDING REQUEST:" . print_r($epayplus_request, true));

			// Process request

			if ($tran->Process()) {
				// Successful payment

				// Send debug email
				$success_text = "RESULT: Card Approved\n";
				$success_text.= "Authcode: " . $tran->authcode . "\n";
				$success_text.= "Result: " . $tran->result . "\n";
				$success_text.= "AVS Result: " . $tran->avs . "\n";
				if ($this->cvv == 'yes') {
					$success_text.= "CVV2 Result: " . $tran->cvv2 . "\n";
				} else {
					$success_text.= "CVV2 not collected\n";
				}

				$this->send_debugging_email( $success_text);

				$order->add_order_note( __('ePay+ payment completed', 'wc-gateway-epayplus') . ' (Result: ' . $tran->result . ')' );
				$order->payment_complete();
				WC()->cart->empty_cart();

				// Empty awaiting payment session
				if ( preg_match('/1\.[0-9]*\.[0-9]*/', WOOCOMMERCE_VERSION )){
					unset($_SESSION['order_awaiting_payment']);
				} else {
					unset( WC()->session->order_awaiting_payment );
				}

				// Return thank you redirect
				return array(
					'result'  => 'success',
					'redirect' => $this->get_return_url( $order )
				);


			} else {

				// Send debug email
				$error_text = "ePay+ Gateway Error.\nResponse reason text: " . $tran->error . "\n";
				$error_text.= "Result: " .$tran->result . "\n";
				if (@$tran->curlerror) $error_text .= "\nCurl Error: ". $tran->curlerror;
				$this->send_debugging_email( $error_text );

				$cancelNote = __('ePay+ payment failed', 'wc-gateway-epayplus') .' '.
					__('Payment was rejected due to an error', 'wc-gateway-epayplus') . ': "' . $tran->error . '". ';

				$order->add_order_note( $cancelNote );
				$this->debug(__('There was an error processing your payment', 'wc-gateway-epayplus') . ': ' . $tran->result . '', 'error');
			}

		}

		/**
		 * Validate the credit card form fields from the Checkout page
		 *
		 * @access public
		 * @return mixed
		 */
		public function validate_fields() {

			$cardName = $this->get_post('epayplus_cardholdername');
			$cardNumber = $this->get_post('epayplus_ccnum');
			$cardCSC = $this->get_post('epayplus_cvv');
			$cardExpirationMonth = $this->get_post('epayplus_expmonth');
			$cardExpirationYear = '20' . $this->get_post('epayplus_expyear');

			if ($this->cvv=='yes') {
				//check security code
				if (!ctype_digit($cardCSC)) {
					$this->debug(__('Card security code is invalid (only digits are allowed)', 'wc-gateway-epayplus'), 'error');
					return false;
				}
			}

			//check expiration data
			$currentYear = date('Y');

			if (!ctype_digit($cardExpirationMonth) || !ctype_digit($cardExpirationYear) ||
				$cardExpirationMonth > 12 ||
				$cardExpirationMonth < 1 ||
				$cardExpirationYear < $currentYear ||
				$cardExpirationYear > $currentYear + 20
			) {
				$this->debug(__('Card expiration date is invalid', 'wc-gateway-epayplus'), 'error');
				return false;
			}

			//check card number
			$cardNumber = str_replace(array(' ', '-'), '', $cardNumber);

			if (empty($cardNumber) || !ctype_digit($cardNumber)) {
				$this->debug(__('Card number is invalid', 'wc-gateway-epayplus'), 'error');
				return false;
			}

			if (empty($cardName)) {
				$this->debug(__('You must enter the name of card holder', 'wc-gateway-epayplus'), 'error');
				return false;
			}

			return true;
		}

		/**
		 * Get post data if set
		 * @param string $name
		 * @return mixed
		 **/
		private function get_post($name) {
			if (isset($_POST[$name])) {
				return $_POST[$name];
			}
			return NULL;
		}

		/**
		 * Output a message or error
		 * @param  string $message
		 * @param  string $type
		 */
		public function debug( $message, $type = 'notice' ) {
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
				wc_add_notice( $message, $type );
			} else {
				wc_add_notice( $message, 'notice' );
			}
		}

		/**
		 * Send debugging email
		 *
		 * @param string $debug
		 * @return null
		 **/
		private function send_debugging_email( $debug ) {

			if ($this->debugon!='yes') return; // Debug must be enabled
			if (!$this->debugrecipient) return; // Recipient needed

			// Send the email
			wp_mail( $this->debugrecipient, __('ePay+ Debug', 'wc-gateway-epayplus'), $debug );

		}

	}

	/**
	 * Plugin page links
	 *
	 * @param array $links
	 * @return array
	 */
	function wc_epayplus_plugin_links( $links ) {
	
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_epayplus' ) . '">' .
			__( 'Settings', 'wc-gateway-epayplus' ) . '</a>',
			'<a href="http://www.ndmscorp.com/epay-plus/">' . __( 'Docs', 'wc-gateway-epayplus' ) . '</a>',
		);
	
		return array_merge( $plugin_links, $links );
	}
	
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_epayplus_plugin_links' );
	
	/**
	 * Add the gateway to woocommerce
	 *
	 * @param array $methods
	 * @return array
	 **/
	function add_epayplus_gateway( $methods ) {
		$methods[] = 'WC_Gateway_Epayplus';
		return $methods;
	}

	add_filter('woocommerce_payment_gateways', 'add_epayplus_gateway' );
}
