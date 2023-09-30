<?php

/**
 * WC_QuickPay_Settings class
 *
 * @class        WC_QuickPay_Settings
 * @version        1.0.0
 * @package        Woocommerce_QuickPay/Classes
 * @category    Class
 * @author        PerfectSolution
 */
class WC_QuickPay_Settings {

	/**
	 * get_fields function.
	 *
	 * Returns an array of available admin settings fields
	 *
	 * @access public static
	 * @return array
	 */
	public static function get_fields() {
		$fields =
			[
				'enabled' => [
					'title' => 'Enable/Disable',
                    'label' => 'Enable Custom Gateway',
					'type'    => 'checkbox',
					'default' => 'yes'
				],
                'direct_payment' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Redirec To QuickPay Payment Page',
                    'type' => 'checkbox',
                    'description' => 'if you enable this it will redirect you to direct quickpay payment page. It will ignore the qr page',
                    'default' => 'no'
                ),
				'iziibuy_api_key'              => [
					'title' => 'Iziibuy Api Key',
					'type'        => 'text',
					'description' => __( 'This api key is from iziibuy website. And this key verify that you have purchase the subscription from the iziibuy website.', 'woo-quickpay' ),
					'desc_tip'    => true,
				],
				'quickpay_apikey'              => [
					'title' => 'Api Key',
					'type'        => 'text',
					'description' => __( 'Your API User\'s key. Create a separate API user in the "Users" tab inside the QuickPay manager.', 'woo-quickpay' ),
					'desc_tip'    => true,
				],
				'quickpay_privatekey'          => [
					'title'       => __( 'Secret key', 'woo-quickpay' ) . self::get_required_symbol(),
					'type'        => 'text',
					'description' => __( 'Your agreement private key. Found in the "Integration" tab inside the QuickPay manager.', 'woo-quickpay' ),
					'desc_tip'    => true,
				],
				'quickpay_autocapture'         => [
					'title'       => __( 'Auto Capture', 'woo-quickpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable', 'woo-quickpay' ),
					'description' => __( 'Enable Auto Capture If you sell services, digital or downloadable goods.', 'woo-quickpay' ),
					'default'     => 'no',
					'desc_tip'    => false,
				],
				'quickpay_autocapture_virtual' => [
					'title'       => __( 'Virtual products', 'woo-quickpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable', 'woo-quickpay' ),
					'description' => __( 'Automatically capture payments on virtual products. If the order contains both physical and virtual products, this setting will be overwritten by the default setting above.', 'woo-quickpay' ),
					'default'     => 'no',
					'desc_tip'    => false,
				],
				'quickpay_captureoncomplete'            => [
					'title'       => __( 'Capture on complete', 'woo-quickpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable', 'woo-quickpay' ),
					'description' => __( 'When enabled quickpay payments will automatically be captured when order state is set to "Complete".', 'woo-quickpay' ),
					'default'     => 'no',
					'desc_tip'    => true,
				],
				'quickpay_complete_on_capture'          => [
					'title'       => __( 'Complete order on capture callbacks', 'woo-quickpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable', 'woo-quickpay' ),
					'description' => __( 'When enabled, an order will be automatically completed when capture callbacks are sent to WooCommerce. Callbacks are sent by QuickPay when the payment is captured from either the shop or the QuickPay manager. Keep disabled to manually complete orders. ', 'woo-quickpay' ),
					'default'     => 'no',
				],
				'quickpay_cancel_transaction_on_cancel' => [
					'title'       => __( 'Cancel payments on order cancellation', 'woo-quickpay' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable', 'woo-quickpay' ),
					'description' => __( 'Automatically cancel payments via the API when an order\'s status changes to cancelled.', 'woo-quickpay' ),
					'default'     => 'no',
				],
				'title'                                 => [
					'title'       => __( 'Title', 'woo-quickpay' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woo-quickpay' ),
					'default'     => __( 'QuickPay', 'woo-quickpay' ),
					'desc_tip'    => true,
				],
				'description'                           => [
					'title'       => __( 'Customer Message', 'woo-quickpay' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description which the user sees during checkout.', 'woo-quickpay' ),
					'default'     => __( 'Pay via QuickPay. Allows you to pay with your credit card via QuickPay.', 'woo-quickpay' ),
					'desc_tip'    => true,
				]
			];

		if ( WC_QuickPay_Subscription::plugin_is_active() ) {
			$fields['woocommerce-subscriptions'] = [
				'type'  => 'title',
				'title' => 'Subscriptions'
			];

			$fields['subscription_autocomplete_renewal_orders'] = [
				'title'       => __( 'Complete renewal orders', 'woo-quickpay' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable', 'woo-quickpay' ),
				'description' => __( 'Automatically mark a renewal order as complete on successful recurring payments.', 'woo-quickpay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			];

			// Creates a subscription transaction on renewal orders and automatically captures payment for it afterwards on callback
			$fields['subscription_update_card_on_manual_renewal_payment'] = [
				'title'       => __( 'Update card on manual renewal payment', 'woo-quickpay' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable', 'woo-quickpay' ),
				'description' => __( 'When paying failed renewals, the payment link will authorize a new subscription transaction which will be saved on the customer\'s subscription. On callback, a payment transaction related to the actual renewal order will be created.', 'woo-quickpay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			];
		}

		return $fields;
	}

	/**
	 * @return array
	 */
	public static function get_card_icons() {
		return [
			'apple-pay'             => 'Apple Pay',
			'dankort'               => 'Dankort',
			'google-pay'            => 'Google Pay',
			'visa'                  => 'Visa',
			'visa-verified'         => 'Verified by Visa',
			'mastercard'            => 'Mastercard',
			'mastercard-securecode' => 'Mastercard SecureCode',
			'mastercard-idcheck'    => 'Mastercard ID Check',
			'maestro'               => 'Maestro',
			'jcb'                   => 'JCB',
			'americanexpress'       => 'American Express',
			'diners'                => 'Diner\'s Club',
			'discovercard'          => 'Discover Card',
			'viabill'               => 'ViaBill',
			'paypal'                => 'Paypal',
			'danskebank'            => 'Danske Bank',
			'nordea'                => 'Nordea',
			'mobilepay'             => 'MobilePay',
			'forbrugsforeningen'    => 'Forbrugsforeningen',
			'ideal'                 => 'iDEAL',
			'unionpay'              => 'UnionPay',
			'sofort'                => 'Sofort',
			'cirrus'                => 'Cirrus',
			'klarna'                => 'Klarna',
			'bankaxess'             => 'BankAxess',
			'vipps'                 => 'Vipps',
			'swish'                 => 'Swish',
			'trustly'               => 'Trustly',
			'paysafecard'           => 'Paysafe Card',
		];
	}


	/**
	 * custom_variable_options function.
	 *
	 * Provides a list of custom variable options used in the settings
	 *
	 * @access private
	 * @return array
	 */
	private static function custom_variable_options() {
		$options = [
			'billing_all_data'  => __( 'Billing: Complete Customer Details', 'woo-quickpay' ),
			'browser_useragent' => __( 'Browser: User Agent', 'woo-quickpay' ),
			'customer_email'    => __( 'Customer: Email Address', 'woo-quickpay' ),
			'customer_phone'    => __( 'Customer: Phone Number', 'woo-quickpay' ),
			'shipping_all_data' => __( 'Shipping: Complete Customer Details', 'woo-quickpay' ),
			'shipping_method'   => __( 'Shipping: Shipping Method', 'woo-quickpay' ),
		];

		asort( $options );

		return $options;
	}

	/**
	 * Clears the log file.
	 *
	 * @return string
	 */
	public static function clear_logs_section() {
		$html = sprintf( '<h3 class="wc-settings-sub-title">%s</h3>', __( 'Debug', 'woo-quickpay' ) );
		// $html .= sprintf( '<a id="wcqp_wiki" class="wcqp-debug-button button button-primary" href="%s" target="_blank">%s</a>', self::get_wiki_link(), __( 'Got problems? Check out the Wiki.', 'woo-quickpay' ) );
		// $html .= sprintf( '<a id="wcqp_logs" class="wcqp-debug-button button" href="%s">%s</a>', WC_QP()->log->get_admin_link(), __( 'View debug logs', 'woo-quickpay' ) );

		// if ( woocommerce_quickpay_can_user_empty_logs() ) {
		// 	$html .= sprintf( '<button role="button" id="wcqp_logs_clear" class="wcqp-debug-button button">%s</button>', __( 'Empty debug logs', 'woo-quickpay' ) );
		// }

		// if ( woocommerce_quickpay_can_user_flush_cache() ) {
		// 	$html .= sprintf( '<button role="button" id="wcqp_flush_cache" class="wcqp-debug-button button">%s</button>', __( 'Empty transaction cache', 'woo-quickpay' ) );
		// }

		// $html .= sprintf( '<br/>' );
		// $html .= sprintf( '<h3 class="wc-settings-sub-title">%s</h3>', __( 'Enable', 'woo-quickpay' ) );

		return $html;
	}

	/**
	 * Returns the link to the gateway settings page.
	 *
	 * @return mixed
	 */
	public static function get_settings_page_url() {
		return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=custom_quickpay_gateway' );
	}

	/**
	 * Shows an admin notice if the setup is not complete.
	 *
	 * @return void
	 */
	public static function show_admin_setup_notices() {
		$error_fields = [];

		$mandatory_fields = [
			'quickpay_privatekey' => __( 'Private key', 'woo-quickpay' ),
			'quickpay_apikey'     => __( 'Api User key', 'woo-quickpay' )
		];

		foreach ( $mandatory_fields as $mandatory_field_setting => $mandatory_field_label ) {
			if ( self::has_empty_mandatory_post_fields( $mandatory_field_setting ) ) {
				$error_fields[] = $mandatory_field_label;
			}
		}

		if ( ! empty( $error_fields ) ) {
			$message = sprintf( '<h2>%s</h2>', __( "IziiPay", 'woo-quickpay' ) );
			$message .= sprintf( '<p>%s</p>', sprintf( __( 'You have missing or incorrect settings. Go to the <a href="%s">settings page</a>.', 'woo-quickpay' ), self::get_settings_page_url() ) );
			$message .= '<ul>';
			foreach ( $error_fields as $error_field ) {
				$message .= "<li>" . sprintf( __( '<strong>%s</strong> is mandatory.', 'woo-quickpay' ), $error_field ) . "</li>";
			}
			$message .= '</ul>';

			printf( '<div class="%s">%s</div>', 'notice notice-error', $message );
		}

	}
	public static function show_invalid_subscription_message() {
		$iziibuy_subscription = get_option('woocommerce_custom_quickpay_gateway_iziibuy_subscription');
		if($iziibuy_subscription != 'yes'){
			$message = sprintf( '<h2>%s</h2>', __( "IziiPay", 'woo-quickpay' ) );
			$message .= sprintf( '<p>%s</p>', sprintf( __( 'You have missing or incorrect settings. Go to the <a href="%s">settings page</a>.', 'woo-quickpay' ), self::get_settings_page_url() ) );
			$message .= '<ul>';
			  $message .= "<li>" . sprintf( __( '<strong>Iziibuy subscription key is invalid or Subscription expired</strong>', 'woo-quickpay' ) ) . "</li>";
			$message .= '</ul>';

			printf( '<div class="%s">%s</div>', 'notice notice-error', $message );
		}
	}

	/**
	 * @return string
	 */
	public static function get_wiki_link() {
		return 'http://quickpay.perfect-solution.dk';
	}

	/**
	 * Logic wrapper to check if some of the mandatory fields are empty on post request.
	 *
	 * @return bool
	 */
	private static function has_empty_mandatory_post_fields( $settings_field ) {
		$post_key    = 'woocommerce_quickpay_' . $settings_field;
		$setting_key = WC_QP()->s( $settings_field );

		return empty( $_POST[ $post_key ] ) && empty( $setting_key );

	}

	/**
	 * @return string
	 */
	private static function get_required_symbol() {
		return '<span style="color: red;">*</span>';
	}
}


?>
