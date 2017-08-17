<?php

class WC_Gateway_Woocommerce_Oplonline extends WC_Payment_Gateway {
    /**
     * Main Construct() for the plugin
     * Setup Oplonline Gateway's id, description and other values
     */
    function __construct() {

        // The global ID for this Payment method
        $this->id = "wc_oplonline";

        // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __( "Oplonline", 'wc-oplonline' );

        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __( "Oplonline Payment Gateway Plug-in for WooCommerce", 'wc-oplonline' );

        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = __( "Oplonline", 'wc-oplonline' );

        // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
        $this->icon = null;

        // Bool. Can be set to true if you want payment fields to show on the checkout
        // if doing a direct integration, which we are doing in this case
        //$this->has_fields = true;
        $this->has_fields = false;

        // Supports the default credit card form
        //$this->supports = array( 'default_credit_card_form' );

        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();

        // After init_settings() is called, you can get the settings and load them into variables, e.g:
        // $this->title = $this->get_option( 'title' );
        $this->init_settings();

        // Turn these settings into variables we can use
        foreach ( $this->settings as $setting_key => $value ) {
            $this->$setting_key = $value;
        }

        // Lets check for SSL
        add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );

        // Save settings
        if ( is_admin() ) {
            // Versions over 2.0
            // Save our administration options. Since we are not going to be doing anything special
            // we have not defined 'process_admin_options' in this class so the method in the parent
            // class will be used instead
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }
    } // End __construct()

    // Build the administration fields for this specific Gateway
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'		=> __( 'Enable / Disable', 'wc-oplonline' ),
                'label'		=> __( 'Enable this payment gateway', 'wc-oplonline' ),
                'type'		=> 'checkbox',
                'default'	=> 'no',
            ),
            'title' => array(
                'title'		=> __( 'Title', 'wc-oplonline' ),
                'type'		=> 'text',
                'desc_tip'	=> __( 'Payment title the customer will see during the checkout process.', 'wc-oplonline' ),
                'default'	=> __( 'Oplonline', 'wc-oplonline' ),
            ),
            'description' => array(
                'title'		=> __( 'Description', 'wc-oplonline' ),
                'type'		=> 'textarea',
                'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'wc-oplonline' ),
                'default'	=> __( 'Pay securely using your credit card with Oplonline.', 'wc-oplonline' ),
                'css'		=> 'max-width:400px;'
            ),
            'active_payment_url' => array(
                'title'		=> __( 'Payment URL', 'wc-oplonline' ),
                'type'		=> 'text',
                'desc_tip'	=> __( 'Payment URL provided by Oplonline', 'wc-oplonline' ),
                'default'	=> __( '', 'wc-oplonline' ),
            ),
            'environment' => array(
                'title'		=> __( 'Oplonline Test Mode', 'wc-oplonline' ),
                'label'		=> __( 'Enable Test Mode', 'wc-oplonline' ),
                'type'		=> 'checkbox',
                'description' => __( 'Place the payment gateway in test mode. only if you have a test payment url', 'wc-oplonline' ),
                'default'	=> 'no',
            ),
            'test_payment_url' => array(
                'title'		=> __( 'Test Payment URL', 'wc-oplonline' ),
                'type'		=> 'text',
                'desc_tip'	=> __( 'Test Payment URL provided by Oplonline', 'wc-oplonline' ),
                'default'	=> __( '', 'wc-oplonline' ),
            )
        );
    }

    // Submit payment and handle response
    public function process_payment( $order_id ) {
        global $woocommerce;

        // Get this Order's information so that we know
        // who to charge and how much
        $customer_order = new WC_Order( $order_id );

        // Are we testing right now or is it a real transaction
        $environment = ( $this->environment == "yes" ) ? 'TRUE' : 'FALSE';

        // Decide which URL to post to
        $environment_url = ( "FALSE" == $environment ) ? $this->active_payment_url : $this->test_payment_url;

        // This is where the fun stuff begins
        $payload = array(
            "fullname=" . $customer_order->billing_first_name . " " . $customer_order->billing_last_name,
            "telephone=" . $customer_order->billing_phone,
            "email=" . $customer_order->billing_email,
            "invoice[1][account]=" . $customer_order->user_id,
            "invoice[1][reference]=" . str_replace( "#", "", $customer_order->get_order_number() ),
            "invoice[1][amount]=" . $customer_order->order_total
        );
        // build url with & in middle
        $fw_value_array = implode('&', $payload);
        // combine with payment_url
        $fw_url = $environment_url . "?" . $fw_value_array;
        return array(
            'result'   => 'success',
            'redirect' => $fw_url,
        );

    }

    // Validate fields
    public function validate_fields() {
        return true;
    }

    // Check if we are forcing SSL on checkout pages
    // Custom function not required by the Gateway
    public function do_ssl_check() {
        if( $this->enabled == "yes" ) {
            if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
                echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) ."</p></div>";
            }
        }
    }

}