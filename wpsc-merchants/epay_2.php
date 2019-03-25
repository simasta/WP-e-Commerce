<?php
/**
 * This is the PayPal Certified 2.0 Gateway.
 * It uses the wpsc_merchant class as a base class which is handy for collating user details and cart contents.
 */

 /*
  * This is the gateway variable $nzshpcrt_gateways, it is used for displaying gateway information on the wp-admin pages and also
  * for internal operations.
  */
$nzshpcrt_gateways[$num] = array(
	'name' =>  __( 'A_ePay Mastercard, Maestro, VISA, VISA ELECTRON', 'wpsc' ),
	'api_version' => 2.0,
	'class_name' => 'wpsc_merchant_ePay_2',
	'has_recurring_billing' => false,
	'wp_admin_cannot_cancel' => true,
	'display_name' => __( 'ePay_2', 'wpsc' ),
	'internalname' => 'wpsc_merchant_ePay_2',
	'form' => 'form_ePay_2',
	'submit_function' => 'submit_ePay_2',
	'payment_type' => 'ePay_2'
);

class wpsc_merchant_ePay_2 extends wpsc_merchant
{
	function submit()
	{
		$parameters = array();
		$transact_url = get_option('transact_url');
		if(get_option('permalink_structure') != '')
			$separator ="?";
		else
			$separator ="&";

		$parameters = array
		(
			'merchantnumber' => get_option('ePay_2_merchantnumber'),
			'windowid' => get_option('ePay_2_windowid', 1),
			'group' => get_option('ePay_2_group'),
			'smsreceipt' => get_option('ePay_2_authsms'),
			'mailreceipt' => get_option('ePay_2_authmail'),
			'instantcapture' => get_option('ePay_2_instantcapture'),
			'ownreceipt' => get_option('ePay_2_ownreceipt'),
			'amount' => $this->cart_data["total_price"]*100,
			'currency' => $this->get_local_currency_code(),
			'language' => $this->get_language(),
			'windowstate' => 3,
			'orderid' => $this->purchase_id,
			'accepturl' => $transact_url.$separator."sessionid=".$this->cart_data['session_id']."&gateway=ePay_2",
			'cancelurl' => get_option('shopping_cart_url'),
			'callbackurl' => add_query_arg('gateway', 'wpsc_merchant_ePay_2', $this->cart_data['notification_url']),
			'cms' => "wpecommerce"
		);
		status_header(302);
		wp_redirect("https://ssl.ditonlinebetalingssystem.dk/integration/ewindow/Default.aspx?" . http_build_query($parameters) . "&hash=" . md5(implode("", array_values($parameters)) . get_option('ePay_2_md5key')));
		exit;
	}
	
	function parse_gateway_notification() {
		global $wpdb;
		
		$this->purchase_id = $_GET['orderid'];
		
		if(strlen(get_option('ePay_2_md5key')) > 0)
		{
			$params = $_GET;
			$var = "";
			
			foreach ($params as $key => $value)
			{
				if($key != "hash")
				{
					$var .= $value;
				}
			}
			
			$genstamp = md5($var . get_option('ePay_2_md5key'));
			
			if($genstamp != $_GET["hash"])
			{
				echo "Error in MD5 data! Please review your passwords in both ePay_2 and your WordPress admin!";
				$this->set_purchase_processed_by_purchid(6);
				exit();
			}
		}

		$this->set_purchase_processed_by_purchid(3);
		$this->set_transaction_details($_GET['txnid'], 3);
		
		echo "OK - " . $_GET["txnid"];
	}
	
	function get_local_currency_code() {
		if ( empty( $this->local_currency_code ) ) {
			global $wpdb;
			$this->local_currency_code = $wpdb->get_var("SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1");
		}

		return $this->local_currency_code;
	}
	
	function get_language()
	{
		$lang = get_bloginfo("language"); 	
		
		$res = "";
		switch($lang)
		{
			case "da_DK":
				return "1";
			case "de_CH":
				return "7";
			case "de_DE":
				return "7";
			case "en_AU":
				return "2";
			case "en_GB":
				return "2";
			case "en_NZ":
				return "2";
			case "en_US":
				return "2";
			case "sv_SE":
				return "3";
			case "nn_NO":
				return "4";
		}
		return "0";
	}
	
}


function submit_ePay_2() {
	if(isset($_POST['ePay_2_merchantnumber']))
		update_option('ePay_2_merchantnumber', $_POST['ePay_2_merchantnumber']);
		
	if(isset($_POST['ePay_2_windowid']))
		update_option('ePay_2_windowid', $_POST['ePay_2_windowid']);
		
	if(isset($_POST['ePay_2_md5key']))
		update_option('ePay_2_md5key', $_POST['ePay_2_md5key']);
		
	if(isset($_POST['ePay_2_group']))
		update_option('ePay_2_group', $_POST['ePay_2_group']);
		
	if(isset($_POST['ePay_2_authsms']))
		update_option('ePay_2_authsms', $_POST['ePay_2_authsms']);
		
	if(isset($_POST['ePay_2_authmail']))
		update_option('ePay_2_authmail', $_POST['ePay_2_authmail']);
		
	if(isset($_POST['ePay_2_instantcapture']))
		update_option('ePay_2_instantcapture', $_POST['ePay_2_instantcapture']);
		
	if(isset($_POST['ePay_2_ownreceipt']))
		update_option('ePay_2_ownreceipt', $_POST['ePay_2_ownreceipt']);

	return true;
}

function form_ePay_2() {
	global $wpdb, $wpsc_gateways;

	$output = "
		<tr>
		  <td>" . __('Merchant number', 'wpsc' ) . "
		  </td>
		  <td>
		  <input type='text' size='40' value='".get_option('ePay_2_merchantnumber')."' name='ePay_2_merchantnumber' />
		  </td>
		</tr>

		<tr>
		  <td>" . __('Window ID', 'wpsc' ) . "
		  </td>
		  <td>
		  <input type='text' size='40' value='".get_option('ePay_2_windowid', 1)."' name='ePay_2_windowid' />
		  </td>
		</tr>

		<tr>
		  <td>" . __('MD5 key', 'wpsc' ) . "
		  </td>
		  <td>
		  <input type='text' size='40' value='".get_option('ePay_2_md5key')."' name='ePay_2_md5key' />
		  </td>
		</tr>
		
		<tr>
		  <td>" . __('Group', 'wpsc' ) . "
		  </td>
		  <td>
		  <input type='text' size='40' value='".get_option('ePay_2_group')."' name='ePay_2_group' />
		  </td>
		</tr>
		
		<tr>
		  <td>" . __('Auth SMS', 'wpsc' ) . "
		  </td>
		  <td>
		  <input type='text' size='40' value='".get_option('ePay_2_authsms')."' name='ePay_2_authsms' />
		  </td>
		</tr>
		
		<tr>
		  <td>" . __('Auth MAIL', 'wpsc' ) . "
		  </td>
		  <td>
		  <input type='text' size='255' value='".get_option('ePay_2_authmail')."' name='ePay_2_authmail' />
		  </td>
		</tr>
		
		<tr>
		  <td>" . __('Instant capture', 'wpsc' ) . "
		  </td>
		  <td>
  				<input type='radio' name='ePay_2_instantcapture' value='1' ". (intval(get_option('ePay_2_instantcapture')) == 1 ? "checked='checked'" : "") ." /> " . __('Yes', 'wpsc' ) . "
				<input type='radio' name='ePay_2_instantcapture' value='0' ". (intval(get_option('ePay_2_instantcapture')) == 0 ? "checked='checked'" : "") ." /> " . __('No', 'wpsc' ) . "
		  </td>
		</tr>
		
		<tr>
		  <td>" . __('Own receipt', 'wpsc' ) . "
		  </td>
		  <td>
  				<input type='radio' name='ePay_2_ownreceipt' value='1' ". (intval(get_option('ePay_2_ownreceipt')) == 1 ? "checked='checked'" : "") ."  /> " . __('Yes', 'wpsc' ) . "
				<input type='radio' name='ePay_2_ownreceipt' value='0' ". (intval(get_option('ePay_2_ownreceipt')) == 0 ? "checked='checked'" : "") ."  /> " . __('No', 'wpsc' ) . "
		  </td>
		</tr>
		";


  	return $output;
}
?>
