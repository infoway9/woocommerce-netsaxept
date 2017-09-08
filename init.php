<?php
/**
 * Plugin Name: Netaxept Payment Gateway
 * Description: Netaxept payment gateway is an woocommerece payment gateway extension
 * Version: 1.0
 * Author: infoway LLC
   Author URI: http://www.infoway.us
 */
if (!defined('NETAXEPT_PLUGIN_PATH'))
    define('NETAXEPT_PLUGIN_PATH', dirname(__FILE__));

if (!defined('NETAXEPT_PLUGIN_URL'))
    define('NETAXEPT_PLUGIN_URL', plugin_dir_url(__FILE__));


require_once NETAXEPT_PLUGIN_PATH . '/netaxept/ClassOrder.php';
require_once NETAXEPT_PLUGIN_PATH . '/netaxept/ClassTerminal.php';
require_once NETAXEPT_PLUGIN_PATH . '/netaxept/ClassRegisterRequest.php';
require_once NETAXEPT_PLUGIN_PATH . '/netaxept/ClassProcessRequest.php';
require_once NETAXEPT_PLUGIN_PATH . '/netaxept/ClassQueryRequest.php';
require_once NETAXEPT_PLUGIN_PATH . '/netaxept/ClassEnvironment.php';

/*require_once NETAXEPT_PLUGIN_PATH . '/netaxept/ClassEnvironment.php';*/

add_action('plugins_loaded', 'nets_gateway_init', 0);

function nets_gateway_init() {
    if (!class_exists('WC_Payment_Gateway'))
        return;

    require_once(NETAXEPT_PLUGIN_PATH . '/wc-nets-gateway.php');

    add_filter('woocommerce_payment_gateways', 'ps_add_nets_gateway');

    function ps_add_nets_gateway($methods) {
        $methods[] = 'WC_Gateway_Nets_Gateway';
        return $methods;
    }

}

