<?php
/**
 * Plugin Name: Bit Payment Gateway for WooCommerce
 * Plugin URI: https://www.zorem.com/shop/woocommerce-bit-payment-gateway/
 * Description: WooCommerce Payment Gateway Plugin for Bit payment method, an Israeli money transfer service/app.
 * Author: Zorem
 * Author URI: http://www.zorem.com/
 * Version: 2.0
 * Text Domain: woo-bit-payment-gateway
 * WC tested up to: 6.7.0
 * WP tested up to: 6.0.1
*/

defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 */
function wc_offline_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Bit_Gateway_Offline';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_offline_add_to_gateways' );


/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_offline_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bit_offline_gateway' ) . '">' . __( 'Configure', 'woo-bit-payment-gateway' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_offline_gateway_plugin_links' );

class Zorem_Bit_Payment_Init {
	/**
     * Constructor.
 	*/
    public function __construct() {
		$this->init();
    }
	
	/*
	* init when class loaded
	*/
	public function init(){
		
		define("BIT_TEMPLATE_PATH", plugin_dir_path(__FILE__)."templates/" );
		add_action( 'init', array( $this, 'register_bit_pending_payment_order_status' ) );
		add_filter( 'wc_order_statuses', array( $this, 'add_bit_pending_payment_order_status' ) );
		add_action( 'woocommerce_after_dashboard_status_widget', array( $this, 'woocommerce_add_order_status_dashboard_widget' ),10, 1 );
		add_action( 'add_meta_boxes', array( $this, 'bit_confirmation_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_bit_confirmation_number' ), 10, 1 );
		add_action( 'admin_head', array( $this, 'woocommerce_bit_payment_style' ) );				
		add_action( 'wp_head', array( $this, 'woocommerce_bit_payment_front_style' ) );
		add_filter( 'woocommerce_reports_order_statuses', array( $this, 'include_bit_payment_order_status_to_reports'), 20, 1 );
		add_filter( 'woocommerce_email_classes', array( &$this, 'custom_init_emails' ) );
	}
	
	function custom_init_emails( $emails ) {
	    // Include the email class file if it's not included already
	    if ( ! isset( $emails[ 'Bit_Payment_Email' ] ) ) {
	        $emails[ 'Bit_Payment_Email' ] = include_once( 'emails/class-bit-payment-email.php' );
	    }
	
	    return $emails;
		
	}
	
	/** 
	* Register new order status		
	**/
	function register_bit_pending_payment_order_status() {
		load_plugin_textdomain( 'woo-bit-payment-gateway', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
		register_post_status( 'wc-bit-payment', array(
			'label'                     => 'Pending Bit',
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Pending Bit <span class="count">(%s)</span>', 'Pending Bit <span class="count">(%s)</span>' )
		) );
	}
	
	//Add a WooCommerce order status (completed, refunded) into the Dashboard status widget
	function woocommerce_add_order_status_dashboard_widget() {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}		
		$wc_bit_payment_count = 0;
	
		foreach ( wc_get_order_types( 'order-count' ) as $type ) {
			$counts            = (array) wp_count_posts( $type );
			$wc_bit_payment_count    += isset( $counts['wc-bit-payment'] ) ? $counts['wc-bit-payment'] : 0;			
		}
		?>
		<li class="wc-bit-paymen-orders">
		<a href="<?php echo admin_url( 'edit.php?post_status=wc-bit-payment&post_type=shop_order' ); ?>">
			<?php
				/* translators: %s: order count */
				printf(
					_n( '<strong>%s order</strong> Pending Bit', '<strong>%s orders</strong> <span style="color:#cd402e;">Pending Bit</span>', $wc_bit_payment_count, 'woocommerce' ),
					$wc_bit_payment_count
				);
			?>
			</a>
		</li>
		<?php
	}
	
	function woocommerce_bit_payment_style(){
	$bit_icon = plugins_url( 'img/bit-icon.png', __FILE__  );		
	?>
		<style type="text/css">
			.order-status.status-bit-payment {
				background: #cd402e;
				color: #fff;
			}
			#woocommerce_dashboard_status .wc_status_list li.wc-bit-paymen-orders a::before{
				background-image: url('<?php echo $bit_icon; ?>');
				content: '';
				width: 35px;
				height: 35px;
				background-size: cover;
			}
			#woocommerce_bit_offline_gateway_description, #woocommerce_bit_offline_gateway_instructions {
				width: 400px !important;
			}
		</style>		
	<?php }
	
	function woocommerce_bit_payment_front_style(){
	?>
		<style type="text/css">
			.bit_order_instruction p {
				margin-bottom: 10px;
				border-bottom: 1px solid #ececec;
				padding-bottom: 10px;
			}
			.payment_method_bit_offline_gateway label img {
				width: 50px;
			}
		</style>		
	<?php 	
	}
	
	// Add to list of WC Order statuses
	function add_bit_pending_payment_order_status( $order_statuses ) {
				
		$order_statuses['wc-bit-payment'] = _x( 'Pending Bit', 'WooCommerce Order status', 'woocommerce' );			
		return $order_statuses;
	}
	
	/*
	Bit Confirmation Meta Box
	*/
	function bit_confirmation_meta_box(){
		
		add_meta_box( 'bit_confirmation_fields', __('Bit confirmation #','woocommerce'),array( $this, 'bit_confirmation_fields' ), 'shop_order', 'side', 'core' );
	}
	function bit_confirmation_fields()
	{
		global $post;

		$meta_field_data = get_post_meta( $post->ID, '_bit_confirmation_id', true ) ? get_post_meta( $post->ID, '_bit_confirmation_id', true ) : '';
	
		echo '<input type="hidden" name="bit_confirmation_field_nonce" value="' . wp_create_nonce() . '">
		<p style="border-bottom:solid 1px #eee;padding-bottom:13px;">
		<input type="text" style="width:250px;";" name="bit_confirmation_name" placeholder="' . $meta_field_data . '" value="' . $meta_field_data . '"></p>';
	}
	function save_bit_confirmation_number( $post_id ) {

        // We need to verify this with the proper authorization (security stuff).

        // Check if our nonce is set.
        if ( ! isset( $_POST[ 'bit_confirmation_field_nonce' ] ) ) {
            return $post_id;
        }
        $nonce = $_REQUEST[ 'bit_confirmation_field_nonce' ];

        //Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce ) ) {
            return $post_id;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // Check the user's permissions.
        if ( 'page' == $_POST[ 'post_type' ] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {

            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
        // --- Its safe for us to save the data ! --- //

        // Sanitize user input  and update the meta field in the database.
        update_post_meta( $post_id, '_bit_confirmation_id', wc_clean($_POST[ 'bit_confirmation_name' ]) );
    }	
	
	//Custom Statuses in admin reports
	
	//Adding the custom order status to the 3 default woocommerce order statuses
	function include_bit_payment_order_status_to_reports( $statuses ){		
		if ( !empty( $statuses ) ) {
			$WC_Bit_Gateway_Offline = new WC_Bit_Gateway_Offline();		
			$display_in_reports = $WC_Bit_Gateway_Offline->display_in_reports;
			if($display_in_reports == 'yes'){
				$statuses[] = 'bit-payment';	
			}		
			return $statuses;
		}
	}	
}
new Zorem_Bit_Payment_Init();

/**
 * Offline Payment Gateway
 *
 * Provides an Offline Payment Gateway; mainly for testing purposes.
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 * @class 		WC_Bit_Gateway_Offline
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		zorem
 */
add_action( 'plugins_loaded', 'wc_offline_gateway_init', 10 , 1);

function wc_offline_gateway_init() {
	
	class WC_Bit_Gateway_Offline extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$this->id                 = 'bit_offline_gateway';
			$this->icon               = apply_filters('woocommerce_offline_icon', plugins_url( 'img/bit-icon.png', __FILE__ ));
			$this->has_fields         = false;
			$this->method_title       = __( 'Bit Payment', 'woo-bit-payment-gateway' );
			$this->method_description = __( '', 'woo-bit-payment-gateway' );
			$this->method_instructions = __( 'Please forward a Bit payment, use an order number in the Note field when making payment.', 'woo-bit-payment-gateway' );
			$this->email_subject = __( 'Your {site_title} order has been received!', 'woo-bit-payment-gateway' );
			$this->email_heading = __( 'Thank you for your order', 'woo-bit-payment-gateway' );
		  
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
		  
			// Define user set variables
			$this->title        = $this->get_option( 'title', $this->method_title );
			$this->description  = $this->get_option( 'description' , $this->method_description );
			$this->receiver_phone  = $this->get_option( 'receiver_phone' );
			$this->instructions = $this->get_option( 'instructions', $this->method_instructions );
			$this->email_subject = $this->get_option( 'email_subject', $this->email_subject );
			$this->email_heading = $this->get_option( 'email_heading', $this->email_heading );
			$this->display_in_reports = $this->get_option( 'display_in_reports', 'yes');
			//echo $this->display_in_reports;exit;
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		  
			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );	
			
		}
	
		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = apply_filters( 'wc_offline_form_fields', array(
		  
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'woo-bit-payment-gateway' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Bit Payment', 'woo-bit-payment-gateway' ),
					'default' => 'yes'
				),
				
				'title' => array(
					'title'       => __( 'Title', 'woo-bit-payment-gateway' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'woo-bit-payment-gateway' ),
					'default'     => __( 'Bit charge', 'woo-bit-payment-gateway' ),
					'desc_tip'    => true,
				),
				
				'description' => array(
					'title'       => __( 'Description', 'woo-bit-payment-gateway' ),
					'type'        => 'textarea',
					'desc'		=> __( 'Payment method description that the customer will see on your checkout.', 'woo-bit-payment-gateway' ),
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'woo-bit-payment-gateway' ),
					'default'     => __( 'Bit Payment - At the end of the order you will receive the Bit Transfer Phone Number and the Order Number for use in the Note field' ),
					'desc_tip'    => true,
				),
				
				'receiver_phone' => array(
					'title'       => __( 'Receiver Phone Number', 'woo-bit-payment-gateway' ),
					'type'        => 'text',
					'description' => __( 'Payments are sent to this number.', 'woo-bit-payment-gateway' ),
					'default'     => __( '', 'woo-bit-payment-gateway' ),
					'desc_tip'    => true,
				),				
				
				'instructions' => array(
					'title'       => __( 'Instructions', 'woo-bit-payment-gateway' ),
					'type'        => 'textarea',
					'description' => __( 'Please forward a Bit payment, use an order number in the Note field when making payment.', 'woo-bit-payment-gateway' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				
				'email_subject' => array(
					'title'       => __( 'Email Subject', 'woo-bit-payment-gateway' ),
					'type'        => 'text',
					'description' => __( 'Available placeholders: {site_title}, {site_address}, {order_date}, {order_number}', 'woo-bit-payment-gateway' ),
					'default'     => __( 'Your {site_title} order has been received!', 'woo-bit-payment-gateway' ),
					'placeholder' => __( 'Your {site_title} order has been received!', 'woo-bit-payment-gateway' ),
					//'desc_tip'    => true,
				),
				
				'email_heading' => array(
					'title'       => __( 'Email Heading', 'woo-bit-payment-gateway' ),
					'type'        => 'text',
					'description' => __( 'Available placeholders: {site_title}, {site_address}, {order_date}, {order_number}', 'woo-bit-payment-gateway' ),
					'default'     => __( 'Thank you for your order', 'woo-bit-payment-gateway' ),
					'placeholder' => __( 'Thank you for your order', 'woo-bit-payment-gateway' ),
					//'desc_tip'    => true,
				),
				'display_in_reports' => array(
					'title'   => __( 'Display Pending Bit orders in reports?', 'woo-bit-payment-gateway' ),
					'type'    => 'checkbox',
					'label'   => __( ' ', 'woo-bit-payment-gateway' ),
					'default' => 'yes'
				),
				
			) );
		}

		/**
		 * Output for the order received page.
		*/
		public function thankyou_page() {
			echo '<div class="bit_order_instruction">';
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );								
			}
			if ( $this->receiver_phone ) {						
				echo wpautop ('<img style="vertical-align: middle;" src="'.plugins_url( 'img/bit-icon.png', __FILE__  ).'" width="50px;" height="50px;">'. wptexturize(__( 'Phone Number:', 'woo-bit-payment-gateway' ). ' <a href="tel:'.$this->receiver_phone.'">'. $this->receiver_phone. '</a>' ) );
			}
			echo '</div>';
		}

		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
			
			if ( $this->description && $this->id === $order->get_payment_method() &&  $sent_to_admin ) {
				echo wpautop( wptexturize( $this->description ) ) . PHP_EOL;
			}
		}	

		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
	
			$order = wc_get_order( $order_id );					
			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'wc-bit-payment' );
			
			WC()->mailer()->emails['WC_Email_New_Order']->trigger( $order->get_id(), $order );
			WC()->mailer()->emails['Bit_Payment_Email']->trigger( $order->get_id(), $order, $this->instructions );			
					
			// Reduce stock levels
			$order->reduce_order_stock();
			
			// Remove cart
			WC()->cart->empty_cart();
			
			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}		
	
	} // end \WC_Bit_Gateway_Offline class
}