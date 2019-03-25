<?php

/**
	* WP eCommerce Test Merchant Gateway
	* This is the file for the test merchant gateway
	*
	* @package wp-e-comemrce
	* @since 3.7.6
	* @subpackage wpsc-merchants
*/
$nzshpcrt_gateways[$num] = array(
	'name' => __( 'Kontant', 'wpsc' ),
	'api_version' => 2.0,
	'class_name' => 'wpsc_merchant_cod',
	'has_recurring_billing' => true,
	'display_name' => __( 'cod', 'wpsc' ),
	'wp_admin_cannot_cancel' => false,
	'requirements' => array(
		 /// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
		///'php_version' => 5.0,
	),

	'form' => 'form_cod',
	'internalname' => 'wpsc_merchant_cod',
);
$image = apply_filters( 'wpsc_merchant_image', '', $nzshpcrt_gateways[$num]['internalname'] );
if ( ! empty( $image ) ) {
	$nzshpcrt_gateways[$num]['image'] = $image;
}
class wpsc_merchant_cod extends wpsc_merchant {

	var $name = 'cod';

	function __construct( $purchase_id = null, $is_receiving = false ) {
		$this->name = __( 'cod Gateway', 'wpsc' );
		parent::__construct( $purchase_id, $is_receiving );
	}

	function submit() {
		$this->set_purchase_processed_by_purchid(2);

	 	$this->go_to_transaction_results($this->cart_data['session_id']);

	 	exit();

	}
}

function form_cod() {
	$output = "
		<tr>
			<td>
				" . __( 'Payment Instructions_cod', 'wpsc' ) . "
			</td>
			<td>
				".__('Enter the payment instructions that you wish to display to your customers when they make a purchase', 'wpsc')."
				<textarea cols='40' rows='9' name='wpsc_options[cod_instructions]'>" . esc_textarea( get_option( 'cod_instructions' ) ) . "</textarea><br />
				<p class='description'>
					".__('For example, this is where you the Shop Owner might enter your cod account details or address so that your customer can make their manual payment.', 'wpsc')."
				</p>
			</td>
		</tr>\n";
	return $output;
}

function _wpsc_filter_cod_merchant_customer_notification_raw_message( $message, $notification ) {
	$purchase_log = $notification->get_purchase_log();

	if ( $purchase_log->get( 'gateway' ) == 'wpsc_merchant_cod' )
		$message = get_option( 'cod_instructions', '' ) . "\r\n" . $message;

	return $message;
}

add_filter(
	'wpsc_purchase_log_customer_notification_raw_message',
	'_wpsc_filter_cod_merchant_customer_notification_raw_message',
	10,
	2
);
add_filter(
	'wpsc_purchase_log_customer_html_notification_raw_message',
	'_wpsc_filter_cod_merchant_customer_notification_raw_message',
	10,
	2
);