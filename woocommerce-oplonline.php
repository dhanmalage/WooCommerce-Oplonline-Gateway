<?php

/*
Plugin Name: WooCommerce OPL online Payment Gateway
Plugin URI:  https://github.com/dmmdust/WooCommerce-Oplonline-Gateway
Description: OPL online Payment gateway plugin for woocommerce
Version:     1.0
Author:      Dhananjaya Maha Malage
Author URI:  http://whenalive.com/
Text Domain: wc-oplonline
Domain Path: /languages
License:     GPL2

WooCommerce OPL online Payment Gateway is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

WooCommerce OPL online Payment Gateway is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WooCommerce OPL online Payment Gateway. If not, see {License URI}.
*/

/**
 * Make sure WooCommerce is active
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

/**
 * you need to create a class after plugins are loaded
 */
add_action( 'plugins_loaded', 'register_wc_oplonline' );

/**
 * It is also important that your gateway class extends the WooCommerce base gateway class
 * You can view the WC_Payment_Gateway class in the API Docs.
 * https://docs.woocommerce.com/wc-apidocs/class-WC_Payment_Gateway.html
 */
function register_wc_oplonline() {

    if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
    /**
     * Localisation
     */
    load_plugin_textdomain('wc-oplonline', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

    /**
     * include the gateway file
     */
    include_once( 'oplonline.php' );

    /**
     * @param $methods
     * @return array
     * As well as defining your class, you need to also tell WooCommerce (WC) that it exists. Do this by filtering woocommerce_payment_gateways:
     */
    function add_woocommerce_oplonline_gateway_class( $methods ) {
        $methods[] = 'WC_Gateway_Woocommerce_Oplonline';
        return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'add_woocommerce_oplonline_gateway_class' );

}



