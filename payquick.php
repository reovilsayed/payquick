<?php
/*
 * Plugin Name: Iziipay Payment Gateway
 * Plugin URI: https://digitalisert.no/
 * Description: This is a custom plugin for Iziipay payment, order your payments here: <p>Check out <a href="https://www.iziipay.com/" target="_blank">Iziipay.com</a>.</p>
 * Author: Digitalisert AS
 * Author URI: https://digitalisert.no
 * Version: 1.0.1
 */
defined('ABSPATH') || exit;

define('WCQP_VERSION', '1.0.1');
define('WCQP_URL', plugins_url(__FILE__));
define('WCQP_PATH', plugin_dir_path(__FILE__));
add_action('plugins_loaded', 'custom_quickpay_gateway_class');

function custom_quickpay_gateway_class()
{
    if (!function_exists('is_woocommerce_active')) {
        require_once WCQP_PATH . 'woo-includes/woo-functions.php';
    }
    // Import helper methods
    require_once WCQP_PATH . 'includes/template.php';

    // Import helper classes
    require_once WCQP_PATH . 'helpers/notices.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-install.php';
    require_once WCQP_PATH . 'classes/api/woocommerce-quickpay-api.php';
    require_once WCQP_PATH . 'classes/api/woocommerce-quickpay-api-transaction.php';
    require_once WCQP_PATH . 'classes/api/woocommerce-quickpay-api-payment.php';
    require_once WCQP_PATH . 'classes/api/woocommerce-quickpay-api-subscription.php';
    require_once WCQP_PATH . 'classes/utils/woocommerce-quickpay-order-utils.php';
    require_once WCQP_PATH . 'classes/utils/woocommerce-quickpay-order-payments-utils.php';
    require_once WCQP_PATH . 'classes/utils/woocommerce-quickpay-order-transaction-data-utils.php';
    require_once WCQP_PATH . 'classes/utils/woocommerce-quickpay-requests-utils.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-module.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-emails.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-admin-ajax.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-admin-orders.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-admin-orders-lists-table.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-admin-orders-meta.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-orders.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-subscriptions.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-subscriptions-change-payment-method.php';
    require_once WCQP_PATH . 'classes/modules/woocommerce-quickpay-subscriptions-early-renewals.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-statekeeper.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-exceptions.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-log.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-helper.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-address.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-settings.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-order.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-subscription.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-countries.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-views.php';
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-callbacks.php';
    require_once WCQP_PATH . 'helpers/permissions.php';
    require_once WCQP_PATH . 'helpers/requests.php';
    require_once WCQP_PATH . 'helpers/transactions.php';

    require_once WCQP_PATH . 'extensions/wpml.php';
    require_once WCQP_PATH . 'extensions/polylang.php';
    class WC_Custom_QuickPay_Gateway extends WC_Payment_Gateway
    {
		public $log;
        public static $_instance = null;
        public static function get_instance()
        {
            if (null === self::$_instance) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
        public function __construct()
        {

            $this->id = 'custom_quickpay_gateway'; // payment gateway  ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'Standard Payment';
            $this->method_description = 'This is a custom payment gateway for quickpay'; // will be displayed on the options page

            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');

            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            // We need custom JavaScript to obtain a token


            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
        }

		public static function filter_load_instances( $methods ) {
			require_once WCQP_PATH . 'classes/instances/instance.php';

			$instances = self::get_gateway_instances();

			foreach ( $instances as $file_name => $class_name ) {
				$file_path = WCQP_PATH . 'classes/instances/' . $file_name . '.php';

				if ( file_exists( $file_path ) ) {
					require_once $file_path;
					$methods[] = $class_name;
				}
			}

			return $methods;
		}


		public function hooks_and_filters() {
			WC_QuickPay_Admin_Ajax::get_instance();
			WC_QuickPay_Admin_Orders::get_instance();
			WC_QuickPay_Admin_Orders_Lists_Table::get_instance();
			WC_QuickPay_Admin_Orders_Meta::get_instance();
			WC_QuickPay_Emails::get_instance();
			WC_QuickPay_Orders::get_instance();
			WC_QuickPay_Subscriptions::get_instance();
			WC_QuickPay_Subscriptions_Change_Payment_Method::get_instance();
			WC_QuickPay_Subscriptions_Early_Renewals::get_instance();

			add_action( 'woocommerce_api_wc_' . $this->id, [ $this, 'callback_handler' ] );
			add_action( 'woocommerce_order_status_completed', [ $this, 'woocommerce_order_status_completed' ] );
			add_action( 'in_plugin_update_message-woocommerce-quickpay/woocommerce-quickpay.php', [ __CLASS__, 'in_plugin_update_message' ] );

			// WooCommerce Subscriptions hooks/filters
			if ( $this->supports( 'subscriptions' ) ) {
				add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, [ $this, 'scheduled_subscription_payment' ], 10, 2 );
				add_action( 'woocommerce_subscription_cancelled_' . $this->id, [ $this, 'subscription_cancellation' ] );
				add_action( 'woocommerce_subscription_payment_method_updated_to_' . $this->id, [ $this, 'on_subscription_payment_method_updated_to_quickpay', ], 10, 2 );
				add_filter( 'wc_subscriptions_renewal_order_data', [ $this, 'remove_renewal_meta_data' ], 10 );
				add_filter( 'woocommerce_subscription_payment_meta', [ $this, 'woocommerce_subscription_payment_meta' ], 10, 2 );
				add_action( 'woocommerce_subscription_validate_payment_meta_' . $this->id, [ $this, 'woocommerce_subscription_validate_payment_meta', ], 10, 2 );
			}

			// WooCommerce Pre-Orders
			add_action( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, [ $this, 'process_pre_order_payments' ] );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );

			// Make sure not to add these actions multiple times
			if ( ! has_action( 'init', 'WC_QuickPay_Helper::load_i18n' ) ) {
				add_action( 'admin_enqueue_scripts', 'WC_QuickPay_Helper::enqueue_stylesheet' );
				add_action( 'admin_enqueue_scripts', 'WC_QuickPay_Helper::enqueue_javascript_backend' );
				add_action( 'wp_ajax_quickpay_run_data_upgrader', 'WC_QuickPay_Install::ajax_run_upgrader' );
				add_action( 'in_plugin_update_message-woocommerce-quickpay/woocommerce-quickpay.php', [ __CLASS__, 'in_plugin_update_message' ] );

				// add_action( 'woocommerce_email_before_order_table', [ $this, 'email_instructions' ], 10, 2 );

				if ( WC_QuickPay_Helper::option_is_enabled( $this->s( 'quickpay_orders_transaction_info', 'yes' ) ) ) {
					add_action( 'woocommerce_quickpay_accepted_callback', [ $this, 'callback_update_transaction_cache' ], 10, 2 );
				}

				//add_action( 'admin_notices', [ $this, 'admin_notices' ] );
			}

			add_action( 'init', 'WC_QuickPay_Helper::load_i18n' );
			add_filter( 'woocommerce_gateway_icon', [ $this, 'apply_gateway_icons' ], 2, 3 );

			// Third party plugins
			add_filter( 'qtranslate_language_detect_redirect', 'WC_QuickPay_Helper::qtranslate_prevent_redirect', 10, 3 );
			add_filter( 'wpss_misc_form_spam_check_bypass', 'WC_QuickPay_Helper::spamshield_bypass_security_check', - 10, 1 );
		}


		/**
		 * s function.
		 *
		 * Returns a setting if set. Introduced to prevent undefined key when introducing new settings.
		 *
		 * @access public
		 *
		 * @param      $key
		 * @param null $default
		 *
		 * @return mixed
		 */
		public function s( $key, $default = null ) {
			if ( isset( $this->settings[ $key ] ) ) {
				return $this->settings[ $key ];
			}

			return apply_filters( 'woocommerce_quickpay_get_setting_' . $key, ! is_null( $default ) ? $default : '', $this );
		}

		/**
		 * Hook used to display admin notices
		 */
		public function admin_notices() {
			WC_QuickPay_Settings::show_admin_setup_notices();
			WC_QuickPay_Install::show_update_warning();
		}


		/**
		 * add_action_links function.
		 *
		 * Adds action links inside the plugin overview
		 *
		 * @access public static
		 * @return array
		 */
		public static function add_action_links( $links ) {
			$links = array_merge( [
				'<a href="' . WC_QuickPay_Settings::get_settings_page_url() . '">' . __( 'Settings', 'woo-quickpay' ) . '</a>',
			], $links );

			return $links;
		}

		/**
		 * Captures one or several transactions when order state changes to complete.
		 *
		 * @param $post_id
		 *
		 * @return void
		 */
		public function woocommerce_order_status_completed( $post_id ): void {
			
			if ( ! $order = woocommerce_quickpay_get_order( $post_id ) ) {
				return ;
			}
		
			// Only run logic on the correct instance to avoid multiple calls, or if all extra instances has not been loaded.
			if (   $this->id !== $order->get_payment_method()) {
				return;
			}

			// Check the gateway settings.
	
			if ( apply_filters( 'woocommerce_quickpay_capture_on_order_completion',true, $order ) ) {
			
				if ( ! WC_QuickPay_Subscription::is_subscription( $order ) ) {
					
					$transaction_id = WC_QuickPay_Order_Utils::get_transaction_id( $order );
					if ( $transaction_id ) {

						try {
							$payment = new WC_QuickPay_API_Payment();

							// Retrieve resource data about the transaction
							$payment->get( $transaction_id );

							// Check if the transaction can be captured
							if ( $payment->is_action_allowed( 'capture' ) ) {
								$amount   = WC_QuickPay_Helper::price_multiplied_to_float( $order->get_total(), $payment->get_currency() ) * 100;
								$payment->capture( $transaction_id, $order, $amount );
							}
						} catch ( QuickPay_Capture_Exception $e ) {
							woocommerce_quickpay_add_runtime_error_notice( $e->getMessage() );
							$order->add_order_note( $e->getMessage() );
							$this->log->add( $e->getMessage() );
						} catch ( \Exception $e ) {
							$error = sprintf( 'Unable to capture payment on order #%s. Problem: %s', $order->get_id(), $e->getMessage() );
							woocommerce_quickpay_add_runtime_error_notice( $error );
							$order->add_order_note( $error );
							$this->log->add( $error );
						}
					}
				}
			}
		}


		/**
		 * Prints out the description of the gateway. Also adds two checkboxes for viaBill/creditcard for customers to choose how to pay.
		 *
		 * @return void
		 */
		public function payment_fields(): void {
			if ( $description = $this->get_description() ) {
				echo wpautop( wptexturize( $description ) );
			}
		}


		/**
		 * Processing payments on checkout
		 *
		 * @param $order_id
		 *
		 * @return array
		 */
		public function process_payment( $order_id ) {
			return $this->prepare_external_window_payment( woocommerce_quickpay_get_order( $order_id ) );
		}

		/**
		 * Processes a payment
		 *
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		private function prepare_external_window_payment( WC_Order $order ) {
			try {
				// Does the order need a new QuickPay payment?
				$needs_payment = true;

				// Default redirect to
				$redirect_to = $this->get_return_url( $order );

				/** @noinspection NotOptimalIfConditionsInspection */
				if ( wc_string_to_bool( WC_QP()->s( 'subscription_update_card_on_manual_renewal_payment' ) ) && WC_QuickPay_Subscription::is_renewal( $order ) ) {
					WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment = true;
				}

				// Instantiate a new transaction
				$api_transaction = woocommerce_quickpay_get_transaction_instance_by_order( $order );

				// If the order is a subscription or an attempt of updating the payment method
				if ( $api_transaction instanceof WC_QuickPay_API_Subscription ) {
					// Clean up any legacy data regarding old payment links before creating a new payment.
					WC_QuickPay_Order_Payments_Utils::delete_payment_id( $order );
					WC_QuickPay_Order_Payments_Utils::delete_payment_link( $order );
				}
				// If the order contains a product switch and does not need a payment, we will skip the QuickPay
				// payment window since we do not need to create a new payment nor modify an existing.
				else if ( WC_QuickPay_Order_Utils::contains_switch_order( $order ) && ! $order->needs_payment() ) {
					$needs_payment = false;
				}

				if ( $needs_payment ) {
					$redirect_to = woocommerce_quickpay_create_payment_link( $order );
				}

				// Perform redirect
				return [
					'result'   => 'success',
					'redirect' => $redirect_to,
				];

			} catch ( QuickPay_Exception $e ) {
				$e->write_to_logs();
				wc_add_notice( $e->getMessage(), 'error' );
			}
		}

		/**
		 * HOOK: Handles pre-order payments
		 */
		public function process_pre_order_payments( $order ): void {
			// Set order object
			$order = woocommerce_quickpay_get_order( $order );

			// Get transaction ID
			$transaction_id = WC_QuickPay_Order_Utils::get_transaction_id( $order );

			// Check if there is a transaction ID
			if ( $transaction_id ) {
				try {
					// Set payment object
					$payment = new WC_QuickPay_API_Payment();
					// Retrieve resource data about the transaction
					$payment->get( $transaction_id );

					// Check if the transaction can be captured
					if ( $payment->is_action_allowed( 'capture' ) ) {
						try {
							// Capture the payment
							$payment->capture( $transaction_id, $order );
						} // Payment failed
						catch ( QuickPay_API_Exception $e ) {
							$this->log->add( sprintf( "Could not process pre-order payment for order: #%s with transaction id: %s. Payment failed. Exception: %s", WC_QuickPay_Order_Utils::get_clean_order_number( $order ), $transaction_id, $e->getMessage() ) );

							$order->update_status( 'failed' );
						}
					}
				} catch ( QuickPay_API_Exception $e ) {
					$this->log->add( sprintf( "Could not process pre-order payment for order: #%s with transaction id: %s. Transaction not found. Exception: %s", WC_QuickPay_Order_Utils::get_clean_order_number( $order ), $transaction_id, $e->getMessage() ) );
				}
			}
		}

		/**
		 * Process refunds
		 * WooCommerce 2.2 or later
		 *
		 * @param int $order_id
		 * @param null|float $amount
		 * @param string $reason
		 *
		 * @return bool|WP_Error
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			try {
				if ( ! $order = woocommerce_quickpay_get_order( $order_id ) ) {
					throw new QuickPay_Exception( sprintf( 'Could not load the order with ID: %d', $order_id ) );
				}

				$transaction_id = WC_QuickPay_Order_Utils::get_transaction_id( $order );

				// Check if there is a transaction ID
				if ( ! $transaction_id ) {
					throw new QuickPay_Exception( sprintf( __( "No transaction ID for order: %s", 'woo-quickpay' ), $order_id ) );
				}

				// Create a payment instance and retrieve transaction information
				$payment = new WC_QuickPay_API_Payment();
				$payment->get( $transaction_id );

				// Check if the transaction can be refunded
				if ( ! $payment->is_action_allowed( 'refund' ) ) {
					if ( in_array( $payment->get_current_type(), [ 'authorize', 'recurring' ], true ) ) {
						throw new QuickPay_Exception( __( 'A non-captured payment cannot be refunded.', 'woo-quickpay' ) );
					}

					throw new QuickPay_Exception( __( 'Transaction state does not allow refunds.', 'woo-quickpay' ) );
				}

				// Perform a refund API request
				$payment->refund( (int) $transaction_id, $order, $amount === null ? null : (float) $amount );

				return true;
			} catch ( QuickPay_Exception $e ) {
				$e->write_to_logs();

				return new WP_Error( 'quickpay_refund_error', $e->getMessage() );
			}
		}

		/**
		 * Clear cart in case its not already done.
		 *
		 * @return void
		 */
		public function thankyou_page() {
			global $woocommerce;
			$woocommerce->cart->empty_cart();
		}
		public function scheduled_subscription_payment( $amount_to_charge, WC_Order $renewal_order ) {
			if ( ( $renewal_order->get_payment_method() === $this->id ) && $renewal_order->needs_payment() ) {
				// Create subscription instance
				$transaction = new WC_QuickPay_API_Subscription();

				/** @var WC_Subscription $subscription */
				// Get the subscription based on the renewal order
				$subscription = WC_QuickPay_Subscription::get_subscriptions_for_renewal_order( $renewal_order, true );

				// Get the transaction ID from the subscription
				$transaction_id = WC_QuickPay_Order_Utils::get_transaction_id( $subscription );

				// Capture a recurring payment with fixed amount
				$response = $this->process_recurring_payment( $transaction, $transaction_id, $amount_to_charge, $renewal_order );

				do_action( 'woocommerce_quickpay_scheduled_subscription_payment_after', $subscription, $renewal_order, $response, $transaction, $transaction_id, $amount_to_charge );

				return $response;
			}
		}
		public function process_recurring_payment( WC_QuickPay_API_Subscription $transaction, $subscription_transaction_id, $amount_to_charge, $order ) {
			$order = woocommerce_quickpay_get_order( $order );

			$response = null;

			try {
				// Capture a recurring payment with fixed amount
				[ $response ] = $transaction->recurring( $subscription_transaction_id, $order, $amount_to_charge );
			} catch ( QuickPay_Exception $e ) {
				WC_QuickPay_Order_Payments_Utils::increase_failed_payment_count( $order );
				// Set the payment as failed
				$order->update_status( 'failed', 'Automatic renewal of ' . $order->get_order_number() . ' failed. Message: ' . $e->getMessage() );

				// Write debug information to the logs
				$e->write_to_logs();
			}

			return $response;
		}
		public function remove_renewal_meta_data( array $meta ): array {
			$avoid_keys = [
				'_quickpay_failed_payment_count',
				'_quickpay_transaction_id',
				'_transaction_id',
				'TRANSACTION_ID', // Prevents the legacy transaction ID from being copied to renewal orders
			];

			foreach ( $avoid_keys as $avoid_key ) {
				if ( ! empty( $meta[ $avoid_key ] ) ) {
					unset( $meta[ $avoid_key ] );
				}
			}

			return $meta;
		}
		public function woocommerce_subscription_payment_meta( $payment_meta, $subscription ): array {
			$payment_meta['quickpay'] = [
				'post_meta' => [
					'_quickpay_transaction_id' => [
						'value' => WC_QuickPay_Order_Utils::get_transaction_id( $subscription ),
						'label' => __( 'QuickPay Transaction ID', 'woo-quickpay' ),
					],
				],
			];

			return $payment_meta;
		}

		public function woocommerce_subscription_validate_payment_meta( $payment_meta, $subscription ) {
			if ( isset( $payment_meta['post_meta']['_quickpay_transaction_id']['value'] ) ) {
				$transaction_id = $payment_meta['post_meta']['_quickpay_transaction_id']['value'];
				// Validate only if the transaction ID has changed
				$sub_transaction_id = WC_QuickPay_Order_Utils::get_transaction_id( $subscription );
				if ( $transaction_id !== $sub_transaction_id ) {
					$transaction = new WC_QuickPay_API_Subscription();
					$transaction->get( $transaction_id );

					// If transaction could be found, add a note on the order for history and debugging reasons.
					$subscription->add_order_note( sprintf( __( 'QuickPay Transaction ID updated from #%d to #%d', 'woo-quickpay' ), $sub_transaction_id, $transaction_id ), 0, true );
				}
			}
		}

		public function on_subscription_payment_method_updated_to_quickpay( $subscription, $old_payment_method ): void {
			WC_QuickPay_Order_Payments_Utils::increase_payment_method_change_count( $subscription );
		}

		public function subscription_cancellation( WC_Order $order ): void {
			if ( 'cancelled' !== $order->get_status() ) {
				return;
			}

			try {
				if ( WC_QuickPay_Subscription::is_subscription( $order ) && apply_filters( 'woocommerce_quickpay_allow_subscription_transaction_cancellation', true, $order, $this ) ) {
					$transaction_id = WC_QuickPay_Order_Utils::get_transaction_id( $order );

					$subscription = new WC_QuickPay_API_Subscription();
					$subscription->get( $transaction_id );

					if ( $subscription->is_action_allowed( 'cancel' ) ) {
						$subscription->cancel( $transaction_id );
					}
				}
			} catch ( QuickPay_Exception $e ) {
				$e->write_to_logs();
			}
		}

		public function on_order_cancellation( $order_id ) {
			$order = new WC_Order( $order_id );

			// Redirect the customer to account page if the current order is failed
			if ( $order->get_status() === 'failed' ) {
				$payment_failure_text = sprintf( __( '<p><strong>Payment failure</strong> A problem with your payment on order <strong>#%i</strong> occured. Please try again to complete your order.</p>', 'woo-quickpay' ), $order_id );

				wc_add_notice( $payment_failure_text, 'error' );

				wp_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
			}

			$order->add_order_note( __( 'QuickPay Payment', 'woo-quickpay' ) . ': ' . __( 'Cancelled during process', 'woo-quickpay' ) );

			wc_add_notice( __( '<p><strong>%s</strong>: %s</p>', __( 'Payment cancelled', 'woo-quickpay' ), __( 'Due to cancellation of your payment, the order process was not completed. Please fulfill the payment to complete your order.', 'woo-quickpay' ) ), 'error' );
		}

		/**
		 * Is called after a payment has been submitted in the QuickPay payment window.
		 *
		 * @return void
		 */
		public function callback_handler(): void {
			// Get callback body
			$request_body = file_get_contents( "php://input" );

			// Decode the body into JSON
			$json = json_decode( $request_body, false, 512, JSON_THROW_ON_ERROR );

			// Instantiate payment object
			$payment = new WC_QuickPay_API_Payment( $json );

			// Fetch order number;
			$order_number = WC_QuickPay_Callbacks::get_order_id_from_callback( $json );
			// Fetch subscription post ID if present
			$subscription_id = WC_QuickPay_Callbacks::get_subscription_id_from_callback( $json );
			$subscription    = null;
			if ( $subscription_id !== null ) {
				$subscription = woocommerce_quickpay_get_subscription( $subscription_id );
			}

			if ( $payment->is_authorized_callback( $request_body ) ) {
				$order = woocommerce_quickpay_get_order( $order_number );
				
				$transaction = end( $json->operations );
				if ( $json->accepted && $order ) {
					do_action( 'woocommerce_quickpay_accepted_callback_before_processing', $order, $json );
					do_action( 'woocommerce_quickpay_accepted_callback_before_processing_status_' . $transaction->type, $order, $json );

					// Perform action depending on the operation status type
					try {
						switch ( $transaction->type ) {
							//
							// Cancel callbacks are currently not supported by the QuickPay API
							//
							case 'cancel' :
								if ( $subscription_id !== null && $subscription ) {
									do_action( 'woocommerce_quickpay_callback_subscription_cancelled', $subscription, $order, $transaction, $json );
								}
								// Write a note to the order history
								$order->add_order_note( __( 'Payment cancelled.', 'woo-quickpay' ) );
								break;

							case 'capture' :
								$order->update_status('wc-completed');
								break;

							case 'refund' :
								$order->add_order_note( sprintf( __( 'Refunded %s %s', 'woo-quickpay' ), WC_QuickPay_Helper::price_normalize( $transaction->amount, $json->currency ), $json->currency ) );
								break;

							case 'recurring':
								WC_QuickPay_Callbacks::payment_authorized( $order, $json );
								break;

							case 'authorize' :
								WC_QuickPay_Callbacks::authorized( $order, $json );
								WC_QuickPay_Callbacks::payment_authorized( $order, $json );
								$order->update_status('wc-processing');
								break;
						}

						do_action( 'woocommerce_quickpay_accepted_callback', $order, $json );
						do_action( 'woocommerce_quickpay_accepted_callback_status_' . $transaction->type, $order, $json );

					} catch ( QuickPay_API_Exception $e ) {
						$e->write_to_logs();
					}
				}

				else {
					$this->log->add( [
						'order'          => $order_number,
						'qp_status_code' => $transaction->qp_status_code,
						'qp_status_msg'  => $transaction->qp_status_msg,
						'aq_status_code' => $transaction->aq_status_code,
						'aq_status_msg'  => $transaction->aq_status_msg,
						'request'        => $request_body,
					] );

					if ( $order && ( $transaction->type === 'recurring' || 'rejected' !== $json->state ) ) {
						$order->update_status( 'failed', sprintf( 'Payment failed <br />QuickPay Message: %s<br />Acquirer Message: %s', $transaction->qp_status_msg, $transaction->aq_status_msg ) );
					}
				}
			} else {
				$this->log->add( sprintf( __( 'Invalid callback body for order #%s.', 'woo-quickpay' ), $order_number ) );
			}
		}
		public function callback_update_transaction_cache( WC_Order $order, $json ): void {
			try {
				// Instantiating a payment transaction.
				// The type of transaction is currently not important for caching - hence no logic for handling subscriptions is added.
				$transaction = new WC_QuickPay_API_Payment( $json );
				$transaction->cache_transaction();
			} catch ( QuickPay_Exception $e ) {
				$this->log->add( sprintf( 'Could not cache transaction from callback for order: #%s -> %s', $order->get_id(), $e->getMessage() ) );
			}
		}
		public function generate_settings_html( $form_fields = array(), $echo = true ) {
			$html = sprintf( "" );


			ob_start();
			do_action( 'woocommerce_quickpay_settings_table_before' );
			$html .= ob_get_clean();

			$html .= parent::generate_settings_html( $form_fields, $echo );

			ob_start();
			do_action( 'woocommerce_quickpay_settings_table_after' );
			$html .= ob_get_clean();

			if ( $echo ) {
				echo $html; // WPCS: XSS ok.
			} else {
				return $html;
			}
		}


		/**
		 * Adds custom text to the order confirmation email.
		 *
		 * @param WC_Order $order
		 * @param boolean $sent_to_admin
		 *
		 * @return void /string/void
		 */
		public function email_instructions( WC_Order $order, bool $sent_to_admin ): void {
			$payment_method = $order->get_payment_method();

			if ( $payment_method !== 'quickpay' || $sent_to_admin || ( $order->get_status() !== 'processing' && $order->get_status() !== 'completed' ) ) {
				return;
			}

			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}


		/**
		 * FILTER: apply_gateway_icons function.
		 *
		 * Sets gateway icons on frontend
		 *
		 * @return void
		 */
		public function apply_gateway_icons( $icon, $id ) {
			if ( $id === $this->id ) {
				$icon = '';

				$icons = $this->s( 'quickpay_icons' );

				if ( ! empty( $icons ) ) {
					$icons_maxheight = $this->gateway_icon_size();

					foreach ( $icons as $key => $item ) {
						$icon .= $this->gateway_icon_create( $item, $icons_maxheight );
					}
				}
			}

			return $icon;
		}


		/**
		 * gateway_icon_create
		 *
		 * Helper to get the a gateway icon image tag
		 *
		 * @access protected
		 * @return string
		 */
		protected function gateway_icon_create( $icon, $max_height ) {
			$icon = str_replace( 'quickpay_', '', $icon );

			$icon = apply_filters( 'woocommerce_quickpay_checkout_gateway_icon', $icon );

			if ( file_exists( __DIR__ . '/assets/images/cards/' . $icon . '.svg' ) ) {
				$icon_url = WC_HTTPS::force_https_url( plugin_dir_url( __FILE__ ) . 'assets/images/cards/' . $icon . '.svg' );
			} else {
				$icon_url = WC_HTTPS::force_https_url( plugin_dir_url( __FILE__ ) . 'assets/images/cards/' . $icon . '.png' );
			}

			$icon_url = apply_filters( 'woocommerce_quickpay_checkout_gateway_icon_url', $icon_url, $icon );

			return '<img src="' . $icon_url . '" alt="' . esc_attr( $this->get_title() ) . '" style="max-height:' . $max_height . '"/>';
		}


		/**
		 * gateway_icon_size
		 *
		 * Helper to get the a gateway icon image max height
		 *
		 * @access protected
		 * @return void
		 */
		protected function gateway_icon_size() {
			$settings_icons_maxheight = $this->s( 'quickpay_icons_maxheight' );

			return ! empty( $settings_icons_maxheight ) ? $settings_icons_maxheight . 'px' : '20px';
		}


		/**
		 * Show plugin changes. Code adapted from W3 Total Cache.
		 *
		 * @param $args
		 *
		 * @return void
		 */
		public static function in_plugin_update_message( $args ): void {
			$transient_name = 'wcqp_upgrade_notice_' . $args['Version'];
			if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {
				$response = wp_remote_get( 'https://plugins.svn.wordpress.org/woocommerce-quickpay/trunk/README.txt' );

				if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
					$upgrade_notice = self::parse_update_notice( $response['body'] );
					set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
				}
			}

			echo wp_kses_post( $upgrade_notice );
		}

		/**
		 *
		 * parse_update_notice
		 *
		 * Parse update notice from readme file.
		 *
		 * @param string $content
		 *
		 * @return string
		 */
		private static function parse_update_notice( $content ): string {
			// Output Upgrade Notice
			$matches        = null;
			$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( WCQP_VERSION, '/' ) . '\s*=|$)~Uis';
			$upgrade_notice = '';

			if ( preg_match( $regexp, $content, $matches ) ) {
				$version = trim( $matches[1] );
				$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

				if ( version_compare( WCQP_VERSION, $version, '<' ) ) {

					$upgrade_notice .= '<div class="wc_plugin_upgrade_notice">';

					foreach ( $notices as $index => $line ) {
						$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
					}

					$upgrade_notice .= '</div> ';
				}
			}

			return wp_kses_post( $upgrade_notice );
		}

		/**
		 * path
		 *
		 * Returns a plugin URL path
		 *
		 * @param $path
		 *
		 * @return string
		 */
		public function plugin_url( $path ): string {
			return plugins_url( $path, __FILE__ );
		}

        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable Custom Gateway',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'default' => 'Credit Card',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'default' => 'Pay with your credit card and vipps.',
                ),
				'quickpay_autocapture'         => [
					'title'       => __( 'Auto Capture', 'woo-quickpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable', 'woo-quickpay' ),
					'description' => __( 'Enable Auto Capture If you have physical products.', 'woo-quickpay' ),
					'default'     => 'no',
					'desc_tip'    => false,
				],
                'api_key' => array(
                    'title' => 'Api Key',
                    'type' => 'text'
                ),
                'secret_key' => array(
                    'title' => 'Secret Key',
                    'type' => 'text'
                )
            );
        }

    }

    /**
     * Make the object available for later use
     *
     * @return WC_Custom_QuickPay_Gateway
     */
    function WC_QP(): WC_Custom_QuickPay_Gateway
    {
        return WC_Custom_QuickPay_Gateway::get_instance();
    }

    // Instantiate
    WC_QP();
    WC_QP()->hooks_and_filters();



    function add_custom_quickpay_gateway_class($methods)
    {
        $methods[] = 'WC_Custom_QuickPay_Gateway';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'add_custom_quickpay_gateway_class');
    add_filter('woocommerce_quickpay_load_instances', 'WC_Custom_QuickPay_Gateway::filter_load_instances');
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'WC_Custom_QuickPay_Gateway::add_action_links');
}

register_activation_hook(__FILE__, static function () {
    require_once WCQP_PATH . 'classes/woocommerce-quickpay-install.php';

    // Run the installer on the first install.
    if (WC_QuickPay_Install::is_first_install()) {
        WC_QuickPay_Install::install();
    }
});

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});





//other code not gateway related
if (!class_exists('Payquick_Plugin_Frontend')) {
    class Payquick_Plugin_Frontend
    {
        public function __construct()
        {
            add_action('plugins_loaded', array($this, 'initialize'), 99);
            register_activation_hook(__FILE__, array($this, 'activate'));
            register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        }

        public function initialize()
        {
            if (!class_exists('WooCommerce')) {
                $error = new WP_Error('woocommerce_not_found', __('WooCommerce is required for activating this plugin.', 'text-domain'));
                wp_die($error);
            }

            add_filter('woocommerce_cart_needs_payment', '__return_false');
            add_action('woocommerce_thankyou', array($this, 'redirect_after_order_create_or_payment'));
            add_filter('theme_page_templates', array($this, 'quickpay_custom_template'));
            add_filter('template_include', array($this, 'load_custom_template'));
            add_filter('wp_enqueue_scripts', array($this, 'custom_payment_enqueue_styles'));
        }

        public function activate()
        {
            $page_template = dirname(__FILE__) . '/custom-payment.php';
            $new_page = array(
                'post_title' => 'Standard Payment',
                'post_content' => 'This page is for Payquick orders. Do not delete this page',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_type' => 'page',
                'post_name' => 'custom-payment',
            );

            if (!get_page_by_path('custom-payment', OBJECT, 'page')) {
                $post_id = wp_insert_post($new_page);
                update_post_meta($post_id, '_wp_page_template', 'custom-payment.php');
            }
        }

        public function deactivate()
        {
            // Plugin deactivation
        }
        public function redirect_after_order_create_or_payment($order_id)
        {
            $order = wc_get_order($order_id);

            $redirect_url = ''; // Replace with the URL of the new page

            if ($order) {
				if($order->get_payment_method() == 'custom_quickpay_gateway'){
					//wp_redirect($order->get_checkout_order_received_url());
				}else{
					$order->update_status('wc-pending');
					$redirect_url = add_query_arg('order_id',$order->get_id(), get_permalink(get_page_by_path('custom-payment')));
					wp_redirect($redirect_url);
					exit;
				}
            }
        }

        public function quickpay_custom_template($templates)
        {
            $templates['custom-payment.php'] = 'QuickPay Custom Template';
            return $templates;
        }
        public function load_custom_template($template)
        {
            if (is_page('custom-payment')) {
                $custom_template = dirname(__FILE__) . '/custom-payment.php';
                if (file_exists($custom_template)) {
                    return $custom_template;
                }
            }
            return $template;
        }
        public function custom_payment_enqueue_styles()
        {
            // Enqueue your custom CSS file
            wp_enqueue_style('custom_quickpay_css', plugin_dir_url(__FILE__) . 'assets/css/style.css');
        }

    }

    // Instantiate the class
    new Payquick_Plugin_Frontend();
}


add_action('init', 'register_payment_confirmation_page');

function register_payment_confirmation_page()
{
    add_rewrite_rule('^payment-confirmation/?$', 'index.php?custom_payment_confirmation_page=payment_confirmation', 'top');
    flush_rewrite_rules();
}

add_filter('query_vars', 'add_payment_confirmation_query_vars');

function add_payment_confirmation_query_vars($vars)
{
    $vars[] = 'custom_payment_confirmation_page';
    return $vars;
}

add_filter('template_redirect', 'payment_confirmation_route');

function payment_confirmation_route()
{
    $custom_page = get_query_var('custom_payment_confirmation_page');
    if ($custom_page === 'payment_confirmation') {
        if (true) {
            $order = wc_get_order(35);
            $redirect_to = woocommerce_quickpay_create_payment_link($order);
           // print_r($redirect_to);
            exit;
        } else {
            wp_redirect();
            exit;
        }
    }
}

add_action('admin_post_custom_payment_form', 'handle_custom_payment_form');
add_action('admin_post_nopriv_custom_payment_form', 'handle_custom_payment_form');

function handle_custom_payment_form()
{
    if ($_POST['payment_method'] == 'quickpay') {
        $order = wc_get_order($_POST['order_id']);
		add_post_meta( $_POST['order_id'], '_payment_method','custom_quickpay_gateway', true );
		add_post_meta( $_POST['order_id'], '_payment_method_title','Standard Payment', true );

        $redirect_to = woocommerce_quickpay_create_payment_link($order);
        wp_redirect( $redirect_to);
    }

    // wp_redirect( home_url('/thank-you') );
    exit;
}