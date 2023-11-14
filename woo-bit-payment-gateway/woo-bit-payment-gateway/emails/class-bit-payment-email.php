<?php 
/**
 * Bit Payment Email
 *
 * An email sent to the admin when an order status is changed to Pending Payment.
 * 
 * @class       Bit_Payment_Email
 * @extends     WC_Email
 *
 */

defined( 'ABSPATH' ) or exit;

class Bit_Payment_Email extends WC_Email {
    
	function __construct() {
		
		// Add email ID, title, description, heading, subject
        $this->id                   = 'bit_pending_payment_email';
        $this->title                = __( 'Bit Payment Email', 'woo-bit-payment-gateway' );
        $this->description          = __( 'This email is received when an order status is changed to Pending.', 'woo-bit-payment-gateway' );
                
        // email template path
        $this->template_html    = 'emails/bit-pending-payment-email.php';
		$this->template_plain    = 'emails/plain/bit-pending-payment-email.php';        
        
        // Triggers for this email
        add_action( 'custom_pending_email_notification', array( $this, 'trigger' ) );        
        
		
        // Call parent constructor
        parent::__construct();
        
        // Other settings
        $this->template_base = BIT_TEMPLATE_PATH;        
		
	}

    // This function collects the data and sends the email
    function trigger( $item_id , $order , $instructions ) {
        $attachments = '';
        $send_email = true;
        // validations
        if ( $item_id && $send_email ) {
            // create an object with item details like name, quantity etc.
            $this->object = $this->create_object( $item_id );

            // replace the merge tags with valid data
            $key = array_search( '{product_title}', $this->find );
            if ( false !== $key ) {
                unset( $this->find[ $key ] );
                unset( $this->replace[ $key ] );
            }
                
            if ( $this->object->order_id ) {
                
                $this->find[]    = '{order_date}';
                $this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );
        
                $this->find[]    = '{order_number}';
                $this->replace[] = $this->object->order_id;
            } else {
                    
                $this->find[]    = '{order_date}';
                $this->replace[] = __( 'N/A', 'woo-bit-payment-gateway' );
        
                $this->find[]    = '{order_number}';
                $this->replace[] = __( 'N/A', 'woo-bit-payment-gateway' );
            }
            
            // send the email
            $this->send( $this->object->billing_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

        }
    }
    
    // Create an object with the data to be passed to the templates
    public static function create_object( $item_id ) {
    
        global $wpdb;
    
        $item_object = new stdClass();
        
		//$order_id = $get_order_id[0]->order_id;
         
		$order_id = $item_id;
        $item_object->order_id = $order_id;
    
        $order = new WC_order( $order_id );
		
        // order date
        $post_data = get_post( $order_id );
        $item_object->order_date = $post_data->post_date;
    
        // qty
        $item_object->qty = wc_get_order_item_meta( $item_id, '_qty' );
        
        // total
        $item_object->total = wc_price( wc_get_order_item_meta( $item_id, '_line_total' ) );

        // email adress
        $item_object->billing_email = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->billing_email : $order->get_billing_email();
    
        // customer ID
        $item_object->customer_id = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->user_id : $order->get_user_id();		
        return $item_object;
    
    }
    
    // return the html content
    function get_content_html() {		
		$order = new WC_order( $this->object->order_id );		
		
		$template = $this->get_template( 'template_html' );			
		$local_file    = $this->get_theme_template_file( $template );
		if ( file_exists( $local_file ) && is_writable( $local_file )){	
			ob_start();		
			wc_get_template( $this->template_html, array(
				'item_data'       => $this->object,
				'order'       => $order,
				'email_heading' => $this->get_heading(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'         => $this,		
			));
		} else{
			ob_start();		
			wc_get_template( $this->template_html, array(
				'item_data'       => $this->object,
				'order'       => $order,
				'email_heading' => $this->get_heading(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'         => $this,		
			), 'my-wc-bit-payment-gateway/', $this->template_base );	
		}
        return ob_get_clean();
    }

    // return the plain content
    function get_content_plain() {
		$order = new WC_order( $this->object->order_id );
        ob_start();		
        wc_get_template( $this->template_plain, array(
            'item_data'       => $this->object,
			'order'       => $order,
            'email_heading' => $this->get_heading(),			
			'email_body' => $this->get_body(),
			'sent_to_admin'      => false,
			'plain_text'         => true,
			'email'      => $this,
            ), 'my-wc-bit-payment-gateway/', $this->template_base );
        return ob_get_clean();
    }
    
    // return the subject
    function get_subject() {		
		
		$WC_Bit_Gateway_Offline = new WC_Bit_Gateway_Offline;
		$subject = $WC_Bit_Gateway_Offline->email_subject;
		       
		
        $order = new WC_order( $this->object->order_id );
        return apply_filters( 'woocommerce_email_subject_bit_payment' . $this->id, $this->format_string( $subject ), $this->object );
        
    }
    
    // return the email heading
    public function get_heading() {        
		
		$WC_Bit_Gateway_Offline = new WC_Bit_Gateway_Offline;
		$heading = $WC_Bit_Gateway_Offline->email_heading;
		       
        $order = new WC_order( $this->object->order_id );
        return apply_filters( 'woocommerce_email_heading_bit_payment' . $this->id, $this->format_string( $heading ), $this->object );
        
    }
	
	// return the email heading
    public function get_body() {
        $woocommerce_bit_offline_gateway_settings = get_option('woocommerce_bit_pending_payment_email_settings');
		$custom_body = $woocommerce_bit_offline_gateway_settings['body'];
		
        if($custom_body){
			$body = $custom_body;
		} else{
			$body = $this->body;
		}
        $order = new WC_order( $this->object->order_id );
        return apply_filters( 'woocommerce_bit_payment_email_body_' . $this->id, $this->format_string( $body ), $this->object );
        
    }
    
    // form fields that are displayed in WooCommerce->Settings->Emails
    function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' 		=> __( 'Enable/Disable', 'woo-bit-payment-gateway' ),
                'type' 			=> 'checkbox',
                'label' 		=> __( 'Enable this email notification', 'woo-bit-payment-gateway' ),
                'default' 		=> 'yes'
            ), 
            'email_type' => array(
                'title' 		=> __( 'Email type', 'woo-bit-payment-gateway' ),
                'type' 			=> 'select',
                'description' 	=> __( 'Choose which format of email to send.', 'woo-bit-payment-gateway' ),
                'default' 		=> 'html',
                'class'			=> 'email_type',
                'options'		=> array(
                    'plain'		 	=> __( 'Plain text', 'woo-bit-payment-gateway' ),
                    'html' 			=> __( 'HTML', 'woo-bit-payment-gateway' ),
                    'multipart' 	=> __( 'Multipart', 'woo-bit-payment-gateway' ),
                )
            )
        );
    }
    
}
return new Bit_Payment_Email();