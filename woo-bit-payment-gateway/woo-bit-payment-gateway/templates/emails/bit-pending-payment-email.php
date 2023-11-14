<?php
/**
 * Bit Pending Payment Email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/bit-pending-payment-email.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$WC_Bit_Gateway_Offline = new WC_Bit_Gateway_Offline;
$instruction = $WC_Bit_Gateway_Offline->instructions;
$receiver_phone	 = 	$WC_Bit_Gateway_Offline->receiver_phone;	
/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ 
?>
<p><?php printf( esc_html__( 'Hi %s,', 'woo-bit-payment-gateway' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
<?php /* translators: %s: Order number */ ?>
<p><?php printf( esc_html__( 'Just to let you know — your order has been received for order number #%s.', 'woo-bit-payment-gateway' ), esc_html( $order->get_order_number() ) ); ?></p>
<p style="margin-bottom: 10px;border-bottom: 1px solid #ececec;padding-bottom: 10px;"><?php echo esc_html__( $instruction ); ?></p>
<p style="margin-bottom: 10px;border-bottom: 1px solid #ececec;padding-bottom: 10px; font-size:16px;"><img style="vertical-align: middle;" src="<?php echo plugins_url( 'img/bit-icon.png', __FILE__  );?>" width="50px;" height="50px;"> <?php printf(esc_html__( 'Phone Number: ', 'woo-bit-payment-gateway' ));  echo '<a href="tel:'.esc_html__( $receiver_phone ).'">'.esc_html__( $receiver_phone ).'</a>'; ?></p>

<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

?>
<p>
<?php esc_html_e( 'Thanks!', 'woo-bit-payment-gateway' ); ?>
</p>
<?php

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
