<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Epayplus_Subscriptions class.
 *
 * @extends WC_Gateway_Epayplus
 */


class WC_Gateway_Epayplus_Subscriptions extends WC_Gateway_Epayplus {

	public function __construct() {

		parent::__construct();
		add_action( 'scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 3 );
		add_filter( 'woocommerce_subscriptions_renewal_order_meta_query', array( $this, 'remove_renewal_order_meta' ), 10, 4 );
		//add_action( 'woocommerce_subscriptions_changed_failing_payment_method_epayplus', array( $this, 'update_failing_payment_method' ), 10, 3 );
		// display the current payment method used for a subscription in the "My Subscriptions" table
		add_filter( 'woocommerce_my_subscriptions_recurring_payment_method', array( $this, 'maybe_render_subscription_payment_method' ), 10, 3 );

	}


	/**
	 * Process the payment compatible with subscriptions
	 *
	 * @param int $order_id
	 * @return int
	 */
	function process_payment( $order_id ) {

		if ( class_exists( 'WC_Subscriptions_Order' ) && WC_Subscriptions_Order::order_contains_subscription( $order_id ) ) {

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

			// Tokenization
			$tran->savecard     = 'true';

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
				"shipcountry" => $order->shipping_country,
				"savecard" => 'true'
			);

			$this->send_debugging_email( "SENDING REQUEST:" . print_r($epayplus_request, true));

			// Process request

			if ($tran->Process()) {
				// Successful payment

				// Store token
				$epayplus_token = $tran->cardref;
				update_post_meta( $order->id, '_epayplus_token', $epayplus_token );

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
				if ( @$tran->curlerror ) $error_text .= "\nCurl Error: ". $tran->curlerror;
				$this->send_debugging_email( $error_text );

				$cancelNote = __('ePay+ payment failed', 'wc-gateway-epayplus') .' '.
				              __('Payment was rejected due to an error', 'wc-gateway-epayplus') . ': "' . $tran->error . '". ';

				$order->add_order_note( $cancelNote );
				$this->debug(__('There was an error processing your payment', 'wc-gateway-epayplus') . ': ' . $tran->result . '', 'error');
			}


		} else {
			return parent::process_payment( $order_id );
		}
	}

	/**
	 * scheduled_subscription_payment function.
	 *
	 * @param $amount_to_charge float The amount to charge.
	 * @param $order WC_Order The WC_Order object of the order which the subscription was purchased in.
	 * @param $product_id int The ID of the subscription product for which this payment relates.
	 * @access public
	 * @return void
	 */
	public function scheduled_subscription_payment( $amount_to_charge, $order, $product_id ) {
		$result = $this->process_subscription_payment( $order, $amount_to_charge );

		if ( is_wp_error( $result ) ) {
			WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order, $product_id );
		} else {
			WC_Subscriptions_Manager::process_subscription_payments_on_order( $order );
		}
	}

	/**
	 * process_subscription_payment function.
	 *
	 * @access public
	 * @param mixed $order
	 * @param int $amount (default: 0)
	 * @return bool
	 */
	public function process_subscription_payment( $order = '', $amount = 0 ) {

		$order_items       = $order->get_items();
		$order_item        = array_shift( $order_items );
		$subscription_name = sprintf( __( 'Subscription for "%s"', 'wc-gateway-epayplus' ), $order_item['name'] ) . ' ' . sprintf( __( '(Order %s)', 'wc-gateway-epayplus' ), $order->get_order_number() );

		if ( is_int( $order ) ) {
			$order = new WC_Order( $order );
		}

		$epayplus_token = get_post_meta( $order->id, '_epayplus_token', true );

		if ( $epayplus_token == '' ) {
			return false;
		}

		// Create request
		$tran = new umTransaction;

		$tran->key = $this->sourcekey;
		$tran->pin = $this->pin;

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

		$tran->command      = $this->command;
		$tran->amount		= $order->order_total;
		$tran->invoice		= $order->id;
		$tran->street		= $order->billing_address_1;
		$tran->zip			= $order->billing_postcode;
		$tran->description	= $this->trandescription;
		$tran->card         = $epayplus_token;
		$tran->exp          = '0000';

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

		// Process request
		if ($tran->Process()) {
			// Successful payment

			$order->add_order_note( __('ePay+ subscription payment complete.', 'wc-gateway-epayplus') . ' (Result: ' . $tran->result . ')' );
			return true;

		} else {

			$cancelNote = __('ePay+ subscription payment failed', 'wc-gateway-epayplus') .' '.
			              __('Payment was rejected due to an error', 'wc-gateway-epayplus') . ': "' . $tran->error . '". ';

			$order->add_order_note( $cancelNote );

			return new WP_Error( 'epayplus_error', __( 'ePay+ Gateway Error: ', 'woocommerce-gateway-stripe' ) . $tran->error );

		}

	}

	/**
	 * Don't transfer customer/token meta when creating a parent renewal order.
	 *
	 * @access public
	 * @param array $order_meta_query MySQL query for pulling the metadata
	 * @param int $original_order_id Post ID of the order being used to purchased the subscription being renewed
	 * @param int $renewal_order_id Post ID of the order created for renewing the subscription
	 * @param string $new_order_role The role the renewal order is taking, one of 'parent' or 'child'
	 * @return void
	 */
	public function remove_renewal_order_meta( $order_meta_query, $original_order_id, $renewal_order_id, $new_order_role ) {
		if ( 'parent' == $new_order_role ) {
			$order_meta_query .= " AND `meta_key` NOT LIKE '_stripe_customer_id' "
			                     .  " AND `meta_key` NOT LIKE '_stripe_token' ";
		}
		return $order_meta_query;
	}

	/**
	 * Update the customer_id for a subscription after using Stripe to complete a payment to make up for
	 * an automatic renewal payment which previously failed.
	 *
	 * @access public
	 * @param WC_Order $original_order The original order in which the subscription was purchased.
	 * @param WC_Order $renewal_order The order which recorded the successful payment (to make up for the failed automatic payment).
	 * @param string $subscription_key A subscription key of the form created by @see WC_Subscriptions_Manager::get_subscription_key()
	 * @return void
	 */
	function update_failing_payment_method( $original_order, $renewal_order, $subscription_key ) {
		$new_customer_id = get_post_meta( $renewal_order->id, '_stripe_customer_id', true );
		update_post_meta( $original_order->id, '_stripe_customer_id', $new_customer_id );
	}

	/**
	 * Render the payment method used for a subscription in the "My Subscriptions" table
	 *
	 * @since 1.7.5
	 * @param string $payment_method_to_display the default payment method text to display
	 * @param array $subscription_details the subscription details
	 * @param WC_Order $order the order containing the subscription
	 * @return string the subscription payment method
	 */
	public function maybe_render_subscription_payment_method( $payment_method_to_display, $subscription_details, WC_Order $order ) {

		// bail for other payment methods
		if ( $this->id !== $order->recurring_payment_method || ! $order->customer_user ) {
			return $payment_method_to_display;
		}

		$stripe_customer = get_post_meta( $order->id, '_stripe_customer_id', true );
		$customer_ids    = get_user_meta( $order->customer_user, '_stripe_customer_id', false );

		foreach ( $customer_ids as $customer_id ) {
			if ( $customer_id['customer_id'] == $stripe_customer ) {
				$payment_method_to_display = sprintf( 'Via card ending in %s', $customer_id['active_card'] );
				break;
			}
		}

		return $payment_method_to_display;
	}
}